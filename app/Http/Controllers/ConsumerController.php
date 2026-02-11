<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Consumer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ConsumerController extends Controller
{
    /**
     * Display a listing of consumers.
     */
    public function index(Request $request)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $query = Consumer::with(['user', 'block'])->latest();

        // Filter by block
        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $consumers = $query->paginate(15)->withQueryString();

        // Get data for filters and form
        $blocks = Block::ordered()->get();
        $nextIdNo = Consumer::generateNextIdNo();

        return view('consumers.index', compact('consumers', 'blocks', 'nextIdNo'));
    }

    /**
     * Store a newly created consumer.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'id_no' => ['nullable', 'string', 'max:10', 'unique:consumers', 'regex:/^[0-9]+$/'],
            'block_id' => ['required', 'exists:blocks,id'],
            'lot_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,disconnected,cut_off,pulled_out'],
        ]);

        // Auto-generate ID if not provided
        $idNo = $validated['id_no'] ?? Consumer::generateNextIdNo();

        // Ensure ID is zero-padded to at least 3 digits
        $idNo = str_pad($idNo, 3, '0', STR_PAD_LEFT);

        // Generate default password: {LastName}@{IDNo}
        $defaultPassword = $this->generateDefaultPassword($validated['last_name'], $idNo);

        DB::transaction(function () use ($validated, $idNo, $defaultPassword) {
            $consumerRole = Role::where('slug', 'consumer')->first();

            $user = User::create([
                'role_id' => $consumerRole->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($defaultPassword),
            ]);

            Consumer::create([
                'user_id' => $user->id,
                'id_no' => $idNo,
                'block_id' => $validated['block_id'],
                'lot_number' => $validated['lot_number'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('consumers.index')->with('success', 'Consumer created successfully. Default password: '.$defaultPassword);
    }

    /**
     * Generate default password from last name and ID number.
     */
    private function generateDefaultPassword(string $lastName, string $idNo): string
    {
        // Clean last name: remove non-alpha chars and capitalize first letter
        $cleanedLastName = ucfirst(preg_replace('/[^a-zA-Z]/', '', $lastName));

        return $cleanedLastName.'@'.$idNo;
    }

    /**
     * Display the specified consumer.
     */
    public function show(Request $request, Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $consumer->load(['user', 'block']);

        // Build ledger data for inline display
        $year = $request->input('year', now()->year);
        $availableYears = $this->getAvailableYears($consumer);
        $ledgerEntries = $this->buildLedgerEntries($consumer, $year);
        $totalDebits = collect($ledgerEntries)->sum('debit');
        $totalCredits = collect($ledgerEntries)->sum('credit');
        $currentBalance = $consumer->bills()->sum('balance');

        return view('consumers.show', compact(
            'consumer', 'ledgerEntries', 'year', 'availableYears',
            'totalDebits', 'totalCredits', 'currentBalance'
        ));
    }

    /**
     * Update the specified consumer.
     */
    public function update(Request $request, Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$consumer->user_id],
            'id_no' => ['required', 'string', 'max:10', 'unique:consumers,id_no,'.$consumer->id, 'regex:/^[0-9]+$/'],
            'block_id' => ['required', 'exists:blocks,id'],
            'lot_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,disconnected,cut_off,pulled_out'],
            'reset_password' => ['nullable', 'boolean'],
        ]);

        // Ensure ID is zero-padded to at least 3 digits
        $idNo = str_pad($validated['id_no'], 3, '0', STR_PAD_LEFT);

        $message = 'Consumer updated successfully.';
        $newPassword = null;

        // Reset password if requested
        if ($request->boolean('reset_password')) {
            $newPassword = $this->generateDefaultPassword($validated['last_name'], $idNo);
            $message = 'Consumer updated successfully. New password: '.$newPassword;
        }

        DB::transaction(function () use ($validated, $consumer, $idNo, $newPassword) {
            $userData = [
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'] ?? null,
            ];

            if ($newPassword) {
                $userData['password'] = Hash::make($newPassword);
            }

            $consumer->user->update($userData);

            $consumer->update([
                'id_no' => $idNo,
                'block_id' => $validated['block_id'],
                'lot_number' => $validated['lot_number'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('consumers.index')->with('success', $message);
    }

    /**
     * Remove the specified consumer.
     */
    public function destroy(Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        DB::transaction(function () use ($consumer) {
            $user = $consumer->user;
            $consumer->delete();
            $user->delete();
        });

        return redirect()->route('consumers.index')->with('success', 'Consumer deleted successfully.');
    }

    /**
     * Display the ledger (transaction history) for a consumer.
     */
    public function ledger(Request $request, Consumer $consumer)
    {
        $user = auth()->user();

        // Access control: Admin/Cashier can view any, Consumer can only view their own
        if ($user->role->slug === 'consumer') {
            if ($consumer->user_id !== $user->id) {
                abort(403);
            }
        } elseif (!in_array($user->role->slug, ['admin', 'cashier'])) {
            abort(403);
        }

        $consumer->load(['user', 'block']);

        // Get year filter (default to current year)
        $year = $request->input('year', now()->year);
        $availableYears = $this->getAvailableYears($consumer);

        // Build ledger entries
        $ledgerEntries = $this->buildLedgerEntries($consumer, $year);

        // Calculate totals
        $totalDebits = collect($ledgerEntries)->sum('debit');
        $totalCredits = collect($ledgerEntries)->sum('credit');
        $currentBalance = $consumer->bills()->sum('balance');

        return view('consumers.ledger', [
            'consumer' => $consumer,
            'ledgerEntries' => $ledgerEntries,
            'year' => $year,
            'availableYears' => $availableYears,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'currentBalance' => $currentBalance,
        ]);
    }

    /**
     * Get available years for the ledger filter.
     */
    private function getAvailableYears(Consumer $consumer): array
    {
        $billYears = $consumer->bills()->selectRaw('YEAR(period_from) as year')->distinct()->pluck('year');
        $paymentYears = $consumer->payments()->selectRaw('YEAR(paid_at) as year')->distinct()->pluck('year');

        $years = $billYears->merge($paymentYears)->unique()->sort()->reverse()->values()->toArray();

        // Ensure current year is included
        if (!in_array(now()->year, $years)) {
            array_unshift($years, now()->year);
        }

        return $years;
    }

    /**
     * Build ledger entries from bills, payments, and maintenance.
     */
    private function buildLedgerEntries(Consumer $consumer, int $year): array
    {
        $entries = [];

        // Get bills for the year
        $bills = $consumer->bills()
            ->whereYear('period_from', $year)
            ->orderBy('period_from')
            ->get();

        foreach ($bills as $bill) {
            $entries[] = [
                'date' => $bill->period_from,
                'type' => 'BILL',
                'description' => 'Water Bill - ' . $bill->billing_period_label,
                'reference' => 'BILL-' . $bill->id,
                'reference_id' => $bill->id,
                'reference_route' => 'bills.show',
                'debit' => (float) $bill->total_amount,
                'credit' => 0,
            ];
        }

        // Get payments for the year
        $payments = $consumer->payments()
            ->whereYear('paid_at', $year)
            ->orderBy('paid_at')
            ->get();

        foreach ($payments as $payment) {
            $description = $payment->isBillPayment()
                ? 'Payment - ' . ($payment->bill?->billing_period_label ?? 'Bill')
                : 'Payment - Maintenance #' . $payment->maintenance_request_id;

            $entries[] = [
                'date' => $payment->paid_at,
                'type' => 'PAYMENT',
                'description' => $description,
                'reference' => $payment->receipt_number,
                'reference_id' => $payment->id,
                'reference_route' => 'payments.show',
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ];
        }

        // Get completed maintenance requests with material costs for the year
        $maintenanceRequests = $consumer->maintenanceRequests()
            ->where('status', 'completed')
            ->where('payment_option', 'charge_to_bill')
            ->whereYear('completed_at', $year)
            ->where('total_material_cost', '>', 0)
            ->orderBy('completed_at')
            ->get();

        foreach ($maintenanceRequests as $mr) {
            $entries[] = [
                'date' => $mr->completed_at,
                'type' => 'MAINTENANCE',
                'description' => 'Maintenance Materials - ' . $mr->request_type,
                'reference' => 'MR-' . str_pad($mr->id, 4, '0', STR_PAD_LEFT),
                'reference_id' => $mr->id,
                'reference_route' => 'maintenance-requests.show',
                'debit' => (float) $mr->total_material_cost,
                'credit' => 0,
            ];
        }

        // Sort by date
        usort($entries, fn($a, $b) => $a['date'] <=> $b['date']);

        // Calculate running balance
        $runningBalance = 0;
        foreach ($entries as &$entry) {
            $runningBalance += $entry['debit'] - $entry['credit'];
            $entry['balance'] = $runningBalance;
        }

        return $entries;
    }
}
