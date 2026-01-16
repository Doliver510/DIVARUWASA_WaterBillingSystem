<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     * For Admin/Cashier: Shows all payments or filtered.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Only Admin and Cashier can view payments list
        if (! in_array($user->role->slug, ['admin', 'cashier'])) {
            abort(403);
        }

        $query = Payment::with(['bill', 'maintenanceRequest', 'consumer.user', 'processedBy']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('paid_at', $request->date);
        }

        // Filter by processor (cashier/admin)
        if ($request->filled('processed_by')) {
            $query->where('processed_by', $request->processed_by);
        }

        // Filter by OR number search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('or_number', 'like', '%'.$search.'%')
                    ->orWhereHas('consumer', function ($cq) use ($search) {
                        $cq->where('id_no', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('consumer.user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%');
                    });
            });
        }

        $payments = $query->orderByDesc('paid_at')->paginate(20);

        // Get processors for filter dropdown (users who have processed payments)
        $processors = User::whereIn('id', Payment::distinct()->pluck('processed_by'))
            ->get();

        // Calculate today's total collections
        $todayTotal = Payment::today()->sum('amount');

        // Calculate total for current filter
        $filteredTotal = (clone $query)->sum('amount');

        return view('payments.index', [
            'payments' => $payments,
            'processors' => $processors,
            'todayTotal' => $todayTotal,
            'filteredTotal' => $filteredTotal,
            'currentDate' => $request->date,
            'currentProcessedBy' => $request->processed_by,
            'currentSearch' => $request->search,
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(StorePaymentRequest $request, Bill $bill): RedirectResponse
    {
        // Ensure bill has balance
        if ($bill->balance <= 0) {
            return back()->with('error', 'This bill has already been fully paid.');
        }

        DB::transaction(function () use ($request, $bill) {
            $amount = (float) $request->amount;
            $balanceBefore = $bill->balance;
            $balanceAfter = max(0, $balanceBefore - $amount);

            // Create payment record
            $payment = Payment::create([
                'or_number' => Payment::generateOrNumber(),
                'payment_type' => Payment::TYPE_BILL,
                'bill_id' => $bill->id,
                'maintenance_request_id' => null,
                'consumer_id' => $bill->consumer_id,
                'processed_by' => Auth::id(),
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => 'cash',
                'remarks' => $request->remarks,
                'paid_at' => now(),
            ]);

            // Update bill payment status
            $bill->recordPayment($amount);
        });

        return redirect()
            ->route('bills.show', $bill)
            ->with('success', 'Payment recorded successfully. Official Receipt generated.');
    }

    /**
     * Display the specified payment (receipt view).
     */
    public function show(Payment $payment): View
    {
        $user = Auth::user();

        // Admin/Cashier can view any receipt
        // Consumer can only view their own receipts
        if ($user->role->slug === 'consumer') {
            if ($payment->consumer->user_id !== $user->id) {
                abort(403);
            }
        } elseif (! in_array($user->role->slug, ['admin', 'cashier'])) {
            abort(403);
        }

        $payment->load(['bill.consumer.user', 'processedBy']);

        return view('payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Display printable Official Receipt.
     */
    public function receipt(Payment $payment): View
    {
        $user = Auth::user();

        // Same access rules as show
        if ($user->role->slug === 'consumer') {
            if ($payment->consumer->user_id !== $user->id) {
                abort(403);
            }
        } elseif (! in_array($user->role->slug, ['admin', 'cashier'])) {
            abort(403);
        }

        // Load relationships based on payment type
        $payment->load(['consumer.user', 'processedBy']);

        if ($payment->isBillPayment()) {
            $payment->load(['bill']);
        } elseif ($payment->isMaintenancePayment()) {
            $payment->load(['maintenanceRequest.maintenanceMaterials.material']);
        }

        // Get association info for receipt header
        $association = [
            'name' => AppSetting::getValue('association_name', 'DIVARUWASA'),
            'full_name' => AppSetting::getValue('association_full_name', 'Diamond Valley Rural Waterworks and Sanitation Association, INC.'),
            'address' => AppSetting::getValue('association_address', ''),
            'tin' => AppSetting::getValue('association_tin', ''),
            'email' => AppSetting::getValue('association_email', ''),
        ];

        return view('payments.receipt', [
            'payment' => $payment,
            'association' => $association,
        ]);
    }

    /**
     * Display daily collection summary for cashier.
     */
    public function dailySummary(Request $request): View
    {
        $user = Auth::user();

        if (! in_array($user->role->slug, ['admin', 'cashier'])) {
            abort(403);
        }

        $date = $request->get('date', now()->toDateString());

        $payments = Payment::with(['bill.consumer.user', 'processedBy'])
            ->whereDate('paid_at', $date)
            ->orderBy('paid_at')
            ->get();

        // Group by processor for summary
        $byProcessor = $payments->groupBy('processed_by')->map(function ($group) {
            return [
                'processor' => $group->first()->processedBy,
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ];
        });

        return view('payments.daily-summary', [
            'payments' => $payments,
            'byProcessor' => $byProcessor,
            'date' => $date,
            'totalAmount' => $payments->sum('amount'),
            'totalCount' => $payments->count(),
        ]);
    }
}
