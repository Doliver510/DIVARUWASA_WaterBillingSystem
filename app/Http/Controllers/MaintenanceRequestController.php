<?php

namespace App\Http\Controllers;

use App\Models\Consumer;
use App\Models\MaintenanceMaterial;
use App\Models\MaintenanceRequest;
use App\Models\Material;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of maintenance requests.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = MaintenanceRequest::with(['consumer.user', 'requestedByUser']);

        // If user is a consumer, only show their requests
        if ($user->role->slug === 'consumer') {
            $consumer = Consumer::where('user_id', $user->id)->first();
            if ($consumer) {
                $query->where('consumer_id', $consumer->id);
            } else {
                $query->whereRaw('1 = 0'); // No results
            }
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('requested_at')->get();

        return view('maintenance.requests.index', [
            'requests' => $requests,
            'statuses' => MaintenanceRequest::STATUSES,
            'currentStatus' => $request->status,
        ]);
    }

    /**
     * Show the form for creating a new request.
     */
    public function create(): View
    {
        $user = Auth::user();
        $consumers = [];

        // Staff can create requests for any consumer
        if (in_array($user->role->slug, ['admin', 'maintenance-staff'])) {
            $consumers = Consumer::with('user')->whereHas('user')->get();
        }

        return view('maintenance.requests.create', [
            'consumers' => $consumers,
            'requestTypes' => MaintenanceRequest::REQUEST_TYPES,
        ]);
    }

    /**
     * Store a newly created request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'request_type' => 'required|in:pipe_leak,meter_replacement,other',
            'description' => 'nullable|string',
        ];

        // Staff must select a consumer
        if (in_array($user->role->slug, ['admin', 'maintenance-staff'])) {
            $rules['consumer_id'] = 'required|exists:consumers,id';
        }

        $validated = $request->validate($rules);

        // If consumer is creating their own request
        if ($user->role->slug === 'consumer') {
            $consumer = Consumer::where('user_id', $user->id)->first();
            if (! $consumer) {
                return redirect()->back()->with('error', 'Consumer profile not found.');
            }
            $validated['consumer_id'] = $consumer->id;
            $validated['requested_by'] = null;
        } else {
            $validated['requested_by'] = $user->id;
        }

        $validated['status'] = 'pending';
        $validated['requested_at'] = now();

        MaintenanceRequest::create($validated);

        return redirect()->route('maintenance-requests.index')
            ->with('success', 'Maintenance request submitted successfully.');
    }

    /**
     * Display the specified request.
     */
    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $user = Auth::user();

        // Check access
        if ($user->role->slug === 'consumer') {
            $consumer = Consumer::where('user_id', $user->id)->first();
            if (! $consumer || $maintenanceRequest->consumer_id !== $consumer->id) {
                abort(403);
            }
        }

        $maintenanceRequest->load(['consumer.user', 'requestedByUser', 'maintenanceMaterials.material']);

        $materials = Material::where('stock_quantity', '>', 0)->orderBy('name')->get();

        return view('maintenance.requests.show', [
            'request' => $maintenanceRequest,
            'materials' => $materials,
            'paymentOptions' => MaintenanceRequest::PAYMENT_OPTIONS,
        ]);
    }

    /**
     * Update the request status.
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'payment_option' => 'required_if:status,completed|nullable|in:pay_now,charge_to_bill',
            'remarks' => 'nullable|string',
        ]);

        $payment = null;

        DB::transaction(function () use ($maintenanceRequest, $validated, &$payment) {
            $maintenanceRequest->status = $validated['status'];
            $maintenanceRequest->remarks = $validated['remarks'] ?? $maintenanceRequest->remarks;

            if ($validated['status'] === 'completed') {
                $maintenanceRequest->payment_option = $validated['payment_option'];
                $maintenanceRequest->completed_at = now();

                // If "Pay Now" and there are material costs, create a payment record with OR
                if ($validated['payment_option'] === 'pay_now' && $maintenanceRequest->total_material_cost > 0) {
                    $payment = Payment::create([
                        'receipt_number' => Payment::generateReceiptNumber(),
                        'payment_type' => Payment::TYPE_MAINTENANCE,
                        'bill_id' => null,
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'consumer_id' => $maintenanceRequest->consumer_id,
                        'processed_by' => Auth::id(),
                        'amount' => $maintenanceRequest->total_material_cost,
                        'balance_before' => $maintenanceRequest->total_material_cost,
                        'balance_after' => 0,
                        'payment_method' => 'cash',
                        'remarks' => 'Payment for maintenance materials - Request #'.$maintenanceRequest->id,
                        'paid_at' => now(),
                    ]);
                }
            }

            if ($validated['status'] === 'cancelled') {
                // Restore stock for all materials used
                foreach ($maintenanceRequest->maintenanceMaterials as $mm) {
                    $mm->material->restoreStock(
                        $mm->quantity,
                        "Restored due to cancelled request #{$maintenanceRequest->id}",
                        $maintenanceRequest->id
                    );
                }
            }

            $maintenanceRequest->save();
        });

        $statusLabel = MaintenanceRequest::STATUSES[$validated['status']];
        $message = "Request marked as {$statusLabel}.";

        // If payment was created, show receipt number
        if ($payment) {
            $message .= " Receipt: {$payment->receipt_number}";

            return redirect()->route('payments.receipt', $payment)
                ->with('success', $message);
        }

        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
            ->with('success', $message);
    }

    /**
     * Add material to a request.
     */
    public function addMaterial(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        if (! $maintenanceRequest->canAddMaterials()) {
            return redirect()->back()->with('error', 'Cannot add materials to this request.');
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $material = Material::findOrFail($validated['material_id']);

        // Check stock availability
        if ($material->stock_quantity < $validated['quantity']) {
            return redirect()->back()
                ->with('error', "Insufficient stock for {$material->name}. Available: {$material->stock_quantity}");
        }

        DB::transaction(function () use ($maintenanceRequest, $material, $validated) {
            // Create the usage record
            MaintenanceMaterial::create([
                'maintenance_request_id' => $maintenanceRequest->id,
                'material_id' => $material->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $material->unit_price,
                'subtotal' => $validated['quantity'] * $material->unit_price,
            ]);

            // Deduct stock with audit logging
            $material->deductStock(
                $validated['quantity'],
                "Used for maintenance request #{$maintenanceRequest->id}",
                'maintenance_request',
                $maintenanceRequest->id
            );

            // Recalculate total cost
            $maintenanceRequest->recalculateTotalCost();
        });

        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
            ->with('success', "Added {$validated['quantity']} {$material->unit} of {$material->name}.");
    }

    /**
     * Remove material from a request.
     */
    public function removeMaterial(MaintenanceRequest $maintenanceRequest, MaintenanceMaterial $material): RedirectResponse
    {
        if (! $maintenanceRequest->canAddMaterials()) {
            return redirect()->back()->with('error', 'Cannot modify materials for this request.');
        }

        DB::transaction(function () use ($maintenanceRequest, $material) {
            // Restore stock with audit logging
            $material->material->restoreStock(
                $material->quantity,
                "Material removed from request #{$maintenanceRequest->id}",
                $maintenanceRequest->id
            );

            // Delete the usage record
            $material->delete();

            // Recalculate total cost
            $maintenanceRequest->recalculateTotalCost();
        });

        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
            ->with('success', 'Material removed from request.');
    }
}
