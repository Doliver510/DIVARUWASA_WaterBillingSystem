<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\Consumer;
use App\Models\WaterRateBracket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillController extends Controller
{
    /**
     * Display a listing of bills (for consumers: their bills, for admin: all).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->role->slug === 'consumer') {
            // Consumer sees their own bills
            $consumer = Consumer::where('user_id', $user->id)->first();

            if (! $consumer) {
                abort(404, 'Consumer profile not found.');
            }

            $bills = Bill::where('consumer_id', $consumer->id)
                ->orderByDesc('billing_period')
                ->get();

            return view('bills.consumer-index', [
                'bills' => $bills,
                'consumer' => $consumer,
            ]);
        }

        // Admin sees all bills with filters
        $query = Bill::with(['consumer.user']);

        if ($request->filled('period')) {
            $query->where('billing_period', $request->period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consumer_id')) {
            $query->where('consumer_id', $request->consumer_id);
        }

        $bills = $query->orderByDesc('billing_period')->get();

        // Get available periods for filter
        $periods = Bill::select('billing_period')
            ->distinct()
            ->orderByDesc('billing_period')
            ->pluck('billing_period');

        return view('bills.index', [
            'bills' => $bills,
            'periods' => $periods,
            'statuses' => Bill::STATUSES,
            'currentPeriod' => $request->period,
            'currentStatus' => $request->status,
        ]);
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): View
    {
        $user = Auth::user();

        // Check access for consumers
        if ($user->role->slug === 'consumer') {
            $consumer = Consumer::where('user_id', $user->id)->first();
            if (! $consumer || $bill->consumer_id !== $consumer->id) {
                abort(403);
            }
        }

        $bill->load(['consumer.user', 'meterReading', 'payments.processedBy']);

        // Get charge breakdown for display
        $chargeBreakdown = WaterRateBracket::getChargeBreakdown($bill->consumption);

        return view('bills.show', [
            'bill' => $bill,
            'chargeBreakdown' => $chargeBreakdown,
        ]);
    }

    /**
     * Display printable bill.
     */
    public function print(Bill $bill): View
    {
        $user = Auth::user();

        // Check access for consumers
        if ($user->role->slug === 'consumer') {
            $consumer = Consumer::where('user_id', $user->id)->first();
            if (! $consumer || $bill->consumer_id !== $consumer->id) {
                abort(403);
            }
        }

        $bill->load(['consumer.user']);

        // Get charge breakdown for display
        $chargeBreakdown = WaterRateBracket::getChargeBreakdown($bill->consumption);

        // Get association info
        $association = [
            'name' => AppSetting::getValue('association_name', 'DIVARUWASA'),
            'full_name' => AppSetting::getValue('association_full_name', 'Diamond Valley Rural Waterworks and Sanitation Association, INC.'),
            'address' => AppSetting::getValue('association_address', ''),
            'tin' => AppSetting::getValue('association_tin', ''),
            'email' => AppSetting::getValue('association_email', ''),
        ];

        return view('bills.print', [
            'bill' => $bill,
            'chargeBreakdown' => $chargeBreakdown,
            'association' => $association,
        ]);
    }
}
