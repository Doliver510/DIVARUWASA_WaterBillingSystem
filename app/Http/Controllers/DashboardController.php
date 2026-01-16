<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminderMail;
use App\Models\Announcement;
use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\Consumer;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with role-appropriate widgets.
     */
    public function index(): View
    {
        $user = Auth::user();
        $role = $user->role->slug;

        // Get announcements for this user's role
        $announcements = Announcement::getCurrentForRole($role);

        $data = [
            'user' => $user,
            'role' => $role,
            'announcements' => $announcements,
        ];

        // Admin Dashboard - Full overview
        if ($role === 'admin') {
            $data = array_merge($data, $this->getAdminData());
        }

        // Cashier Dashboard - Payment focused
        if ($role === 'cashier') {
            $data = array_merge($data, $this->getCashierData());
        }

        // Meter Reader Dashboard
        if ($role === 'meter-reader') {
            $data = array_merge($data, $this->getMeterReaderData());
        }

        // Maintenance Staff Dashboard
        if ($role === 'maintenance-staff') {
            $data = array_merge($data, $this->getMaintenanceData());
        }

        // Consumer Dashboard
        if ($role === 'consumer') {
            $data = array_merge($data, $this->getConsumerData($user));
        }

        return view('dashboard', $data);
    }

    /**
     * Get admin dashboard data.
     */
    private function getAdminData(): array
    {
        $currentPeriod = now()->format('Y-m');
        $currentYear = now()->year;

        // Consumer stats
        $totalConsumers = Consumer::count();
        $activeConsumers = Consumer::where('status', 'Active')->count();

        // Today's collections
        $todayCollections = Payment::today()->sum('amount');
        $todayTransactions = Payment::today()->count();

        // This month's billing
        $thisMonthBills = Bill::where('billing_period', $currentPeriod)->get();
        $totalBilled = $thisMonthBills->sum('total_amount');
        $totalCollected = $thisMonthBills->sum('amount_paid');
        $totalOutstanding = $thisMonthBills->sum('balance');

        // Arrears (all unpaid across all periods)
        $totalArrears = Bill::where('balance', '>', 0)->sum('balance');
        $consumersWithArrears = Consumer::whereHas('bills', fn ($q) => $q->where('balance', '>', 0))->count();

        // Recent payments
        $recentPayments = Payment::with(['consumer.user', 'processedBy'])
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get();

        // Pending maintenance requests
        $pendingMaintenance = MaintenanceRequest::where('status', 'pending')->count();

        // Bill status breakdown (counts)
        $billStatusCounts = [
            'paid' => Bill::where('billing_period', $currentPeriod)->where('status', 'paid')->count(),
            'partial' => Bill::where('billing_period', $currentPeriod)->where('status', 'partial')->count(),
            'unpaid' => Bill::where('billing_period', $currentPeriod)->where('status', 'unpaid')->count(),
            'overdue' => Bill::where('billing_period', $currentPeriod)->where('status', 'overdue')->count(),
        ];

        // Bills needing payment reminders
        $billsNeedingReminders = self::getBillsNeedingReminders();

        // === CHART DATA ===

        // Monthly consumption (last 6 months)
        $monthlyConsumption = $this->getMonthlyConsumption(6);

        // Monthly collections (last 6 months)
        $monthlyCollections = $this->getMonthlyCollections(6);

        // This month's consumption total
        $thisMonthConsumption = MeterReading::where('billing_period', $currentPeriod)->sum('consumption');

        // This year's consumption total
        $thisYearConsumption = MeterReading::whereYear('reading_date', $currentYear)->sum('consumption');

        // Collection breakdown by category (this month)
        $collectionBreakdown = $this->getCollectionBreakdown($currentPeriod);

        // Sales breakdown by source
        $salesBreakdown = $this->getSalesBreakdown();

        // Bill payment status amounts (not counts)
        $billPaymentAmounts = [
            'paid' => Bill::where('billing_period', $currentPeriod)->where('status', 'paid')->sum('amount_paid'),
            'partial' => Bill::where('billing_period', $currentPeriod)->where('status', 'partial')->sum('amount_paid'),
            'unpaid' => Bill::where('billing_period', $currentPeriod)->whereIn('status', ['unpaid', 'overdue'])->sum('balance'),
        ];

        return compact(
            'totalConsumers',
            'activeConsumers',
            'todayCollections',
            'todayTransactions',
            'totalBilled',
            'totalCollected',
            'totalOutstanding',
            'totalArrears',
            'consumersWithArrears',
            'recentPayments',
            'pendingMaintenance',
            'billStatusCounts',
            'currentPeriod',
            'billsNeedingReminders',
            'monthlyConsumption',
            'monthlyCollections',
            'thisMonthConsumption',
            'thisYearConsumption',
            'collectionBreakdown',
            'salesBreakdown',
            'billPaymentAmounts'
        );
    }

    /**
     * Get monthly consumption data for charts.
     */
    private function getMonthlyConsumption(int $months): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $period = $date->format('Y-m');
            $consumption = MeterReading::where('billing_period', $period)->sum('consumption');
            $data[] = [
                'month' => $date->format('M Y'),
                'period' => $period,
                'consumption' => (int) $consumption,
            ];
        }

        return $data;
    }

    /**
     * Get monthly collections data for charts.
     */
    private function getMonthlyCollections(int $months): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $collections = Payment::whereBetween('paid_at', [$startOfMonth, $endOfMonth])->sum('amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'amount' => (float) $collections,
            ];
        }

        return $data;
    }

    /**
     * Get collection breakdown by category for current period.
     */
    private function getCollectionBreakdown(string $period): array
    {
        // Water charges collected (from bill payments)
        $waterCollected = DB::table('payments')
            ->join('bills', 'payments.bill_id', '=', 'bills.id')
            ->where('bills.billing_period', $period)
            ->where('payments.payment_type', 'bill')
            ->sum('payments.amount');

        // Penalties collected
        $penaltiesCollected = Bill::where('billing_period', $period)
            ->where('status', 'paid')
            ->sum('penalty');

        // Materials collected (maintenance payments)
        $materialsCollected = Payment::where('payment_type', 'maintenance')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            'water' => (float) $waterCollected,
            'penalties' => (float) $penaltiesCollected,
            'materials' => (float) $materialsCollected,
        ];
    }

    /**
     * Get sales breakdown by source (all time or current year).
     */
    private function getSalesBreakdown(): array
    {
        $currentYear = now()->year;

        // Total water bills collected this year
        $waterSales = DB::table('payments')
            ->join('bills', 'payments.bill_id', '=', 'bills.id')
            ->where('payments.payment_type', 'bill')
            ->whereYear('payments.paid_at', $currentYear)
            ->sum('payments.amount');

        // Total penalties collected this year
        $penaltySales = Bill::whereYear('created_at', $currentYear)
            ->whereIn('status', ['paid', 'partial'])
            ->sum('penalty');

        // Total materials sold this year
        $materialSales = Payment::where('payment_type', 'maintenance')
            ->whereYear('paid_at', $currentYear)
            ->sum('amount');

        return [
            'water' => (float) $waterSales,
            'penalties' => (float) $penaltySales,
            'materials' => (float) $materialSales,
            'total' => (float) ($waterSales + $penaltySales + $materialSales),
        ];
    }

    /**
     * Get cashier dashboard data.
     */
    private function getCashierData(): array
    {
        $todayCollections = Payment::today()->sum('amount');
        $todayTransactions = Payment::today()->count();

        // My collections today
        $myCollections = Payment::today()->where('processed_by', Auth::id())->sum('amount');
        $myTransactions = Payment::today()->where('processed_by', Auth::id())->count();

        // Recent payments
        $recentPayments = Payment::with(['consumer.user', 'bill'])
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        // Pending bills (unpaid/partial)
        $pendingBills = Bill::with(['consumer.user'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->limit(10)
            ->get();

        return compact(
            'todayCollections',
            'todayTransactions',
            'myCollections',
            'myTransactions',
            'recentPayments',
            'pendingBills'
        );
    }

    /**
     * Get meter reader dashboard data.
     */
    private function getMeterReaderData(): array
    {
        $currentPeriod = now()->format('Y-m');

        // Get assigned blocks
        $assignedBlocks = Auth::user()->assignedBlocks;

        // Total consumers in assigned blocks
        $assignedConsumers = Consumer::whereIn('block_id', $assignedBlocks->pluck('id'))
            ->where('status', 'Active')
            ->count();

        // Readings entered this period
        $readingsThisPeriod = MeterReading::where('read_by', Auth::id())
            ->where('billing_period', $currentPeriod)
            ->count();

        // Pending readings (consumers without reading this period)
        $consumersWithReading = MeterReading::where('billing_period', $currentPeriod)
            ->whereIn('consumer_id', Consumer::whereIn('block_id', $assignedBlocks->pluck('id'))->pluck('id'))
            ->pluck('consumer_id');

        $pendingReadings = Consumer::whereIn('block_id', $assignedBlocks->pluck('id'))
            ->where('status', 'Active')
            ->whereNotIn('id', $consumersWithReading)
            ->count();

        return compact(
            'assignedBlocks',
            'assignedConsumers',
            'readingsThisPeriod',
            'pendingReadings',
            'currentPeriod'
        );
    }

    /**
     * Get maintenance staff dashboard data.
     */
    private function getMaintenanceData(): array
    {
        // Request counts by status
        $pendingRequests = MaintenanceRequest::where('status', 'pending')->count();
        $inProgressRequests = MaintenanceRequest::where('status', 'in_progress')->count();
        $completedThisMonth = MaintenanceRequest::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->count();

        // Recent requests
        $recentRequests = MaintenanceRequest::with(['consumer.user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return compact(
            'pendingRequests',
            'inProgressRequests',
            'completedThisMonth',
            'recentRequests'
        );
    }

    /**
     * Get consumer dashboard data.
     */
    private function getConsumerData($user): array
    {
        $consumer = Consumer::where('user_id', $user->id)->first();

        if (! $consumer) {
            return ['consumer' => null];
        }

        // Current bill
        $currentBill = Bill::where('consumer_id', $consumer->id)
            ->orderByDesc('billing_period')
            ->first();

        // Total arrears
        $totalArrears = Bill::where('consumer_id', $consumer->id)
            ->where('balance', '>', 0)
            ->sum('balance');

        // Payment history
        $recentPayments = Payment::where('consumer_id', $consumer->id)
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get();

        // Maintenance requests
        $pendingRequests = MaintenanceRequest::where('consumer_id', $consumer->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return compact(
            'consumer',
            'currentBill',
            'totalArrears',
            'recentPayments',
            'pendingRequests'
        );
    }

    /**
     * Send payment reminder emails manually (Admin only).
     */
    public function sendPaymentReminders(): RedirectResponse
    {
        // Check admin role
        if (Auth::user()->role->slug !== 'admin') {
            abort(403);
        }

        $today = now()->startOfDay();
        $reminderDate = $today->copy()->addDays(3);
        $queuedCount = 0;

        // 1. Bills due in 3 days (upcoming)
        $upcomingBills = Bill::with('consumer.user')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '>=', $today)
            ->whereDate('due_date_end', '<=', $reminderDate)
            ->whereHas('consumer.user', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->get();

        foreach ($upcomingBills as $bill) {
            Mail::to($bill->consumer->user->email)->queue(new PaymentReminderMail($bill, 'upcoming'));
            $queuedCount++;
        }

        // 2. Overdue bills (apply penalty first, then send)
        $overdueBills = Bill::with('consumer.user')
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '<', $today)
            ->whereHas('consumer.user', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->get();

        foreach ($overdueBills as $bill) {
            // Apply penalty if not already applied
            if ($bill->penalty <= 0) {
                $penaltyFee = (float) AppSetting::getValue('penalty_fee', 50);
                $bill->penalty = $penaltyFee;
                $bill->total_amount += $penaltyFee;
                $bill->balance = $bill->total_amount - $bill->amount_paid;
                $bill->status = 'overdue';
                $bill->save();
            }

            Mail::to($bill->consumer->user->email)->queue(new PaymentReminderMail($bill, 'penalty_day'));
            $queuedCount++;
        }

        if ($queuedCount === 0) {
            return redirect()->route('dashboard')
                ->with('info', 'No bills need payment reminders at this time.');
        }

        return redirect()->route('dashboard')
            ->with('success', "Queued {$queuedCount} payment reminder(s). Make sure queue worker is running!");
    }

    /**
     * Get count of bills needing reminders (for dashboard display).
     */
    public static function getBillsNeedingReminders(): int
    {
        $today = now()->startOfDay();
        $reminderDate = $today->copy()->addDays(3);

        // Upcoming (due within 3 days)
        $upcoming = Bill::whereIn('status', ['unpaid', 'partial'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '>=', $today)
            ->whereDate('due_date_end', '<=', $reminderDate)
            ->count();

        // Overdue
        $overdue = Bill::whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '<', $today)
            ->count();

        return $upcoming + $overdue;
    }
}
