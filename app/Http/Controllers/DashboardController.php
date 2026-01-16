<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bill;
use App\Models\Consumer;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
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
        $previousPeriod = now()->subMonth()->format('Y-m');

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

        // Bill status breakdown
        $billStatusCounts = [
            'paid' => Bill::where('billing_period', $currentPeriod)->where('status', 'paid')->count(),
            'partial' => Bill::where('billing_period', $currentPeriod)->where('status', 'partial')->count(),
            'unpaid' => Bill::where('billing_period', $currentPeriod)->where('status', 'unpaid')->count(),
            'overdue' => Bill::where('billing_period', $currentPeriod)->where('status', 'overdue')->count(),
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
            'currentPeriod'
        );
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
}
