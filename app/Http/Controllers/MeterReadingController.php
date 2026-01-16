<?php

namespace App\Http\Controllers;

use App\Models\Consumer;
use App\Models\MeterReading;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of meter readings.
     */
    public function index(Request $request): View
    {
        $query = MeterReading::with(['consumer.user', 'readBy']);

        // Filter by billing period
        if ($request->filled('period')) {
            $query->where('billing_period', $request->period);
        } else {
            // Default to current billing period
            $query->where('billing_period', MeterReading::getCurrentBillingPeriod());
        }

        // Filter by consumer
        if ($request->filled('consumer_id')) {
            $query->where('consumer_id', $request->consumer_id);
        }

        $readings = $query->orderBy('created_at', 'desc')->get();

        // Get list of consumers for the entry form
        $consumers = Consumer::with('user')
            ->whereHas('user')
            ->where('status', 'Active')
            ->orderBy('id_no')
            ->get();

        // Get available billing periods for filter
        $periods = MeterReading::select('billing_period')
            ->distinct()
            ->orderByDesc('billing_period')
            ->pluck('billing_period');

        // Add current period if not in list
        $currentPeriod = MeterReading::getCurrentBillingPeriod();
        if (! $periods->contains($currentPeriod)) {
            $periods = $periods->prepend($currentPeriod);
        }

        return view('meter-readings.index', [
            'readings' => $readings,
            'consumers' => $consumers,
            'periods' => $periods,
            'currentPeriod' => $request->period ?? $currentPeriod,
        ]);
    }

    /**
     * Store a newly created meter reading.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consumer_id' => 'required|exists:consumers,id',
            'reading_value' => 'required|integer|min:0',
            'reading_date' => 'required|date',
            'billing_period' => 'required|regex:/^\d{4}-\d{2}$/',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Check if reading already exists for this consumer and period
        $existing = MeterReading::where('consumer_id', $validated['consumer_id'])
            ->where('billing_period', $validated['billing_period'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'A reading already exists for this consumer in this billing period. Please edit the existing reading instead.')
                ->withInput();
        }

        // Get previous reading
        $previousReading = MeterReading::getPreviousReading($validated['consumer_id']);

        // Validate that current reading is >= previous reading
        if ($validated['reading_value'] < $previousReading) {
            return redirect()->back()
                ->with('error', "Reading value cannot be less than previous reading ({$previousReading} cubic meters).")
                ->withInput();
        }

        $reading = MeterReading::create([
            'consumer_id' => $validated['consumer_id'],
            'reading_value' => $validated['reading_value'],
            'previous_reading' => $previousReading,
            'reading_date' => $validated['reading_date'],
            'billing_period' => $validated['billing_period'],
            'read_by' => Auth::id(),
            'remarks' => $validated['remarks'],
        ]);

        // Auto-generate bill for this reading
        $billingService = new \App\Services\BillingCalculatorService;
        $bill = $billingService->generateBillFromReading($reading);

        $consumer = Consumer::with('user')->find($validated['consumer_id']);
        $consumption = $validated['reading_value'] - $previousReading;

        return redirect()->route('meter-readings.index', ['period' => $validated['billing_period']])
            ->with('success', "Reading recorded for {$consumer->user->full_name}: {$consumption} cubic meters. Bill #".$bill->id.' generated (₱'.number_format($bill->total_amount, 2).').');
    }

    /**
     * Update the specified meter reading.
     */
    public function update(Request $request, MeterReading $meterReading): RedirectResponse
    {
        // Check if reading can be edited
        if (! $meterReading->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This reading has already been billed and cannot be modified.');
        }

        $validated = $request->validate([
            'reading_value' => 'required|integer|min:0',
            'reading_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Validate that current reading is >= previous reading
        if ($validated['reading_value'] < $meterReading->previous_reading) {
            return redirect()->back()
                ->with('error', "Reading value cannot be less than previous reading ({$meterReading->previous_reading} cubic meters).");
        }

        $meterReading->update($validated);

        // Regenerate bill if exists
        $bill = \App\Models\Bill::where('meter_reading_id', $meterReading->id)->first();
        if ($bill) {
            $billingService = new \App\Services\BillingCalculatorService;
            $billingService->regenerateBill($bill, $meterReading);
        }

        return redirect()->route('meter-readings.index', ['period' => $meterReading->billing_period])
            ->with('success', 'Reading updated successfully.'.($bill ? ' Bill recalculated.' : ''));
    }

    /**
     * Remove the specified meter reading.
     */
    public function destroy(MeterReading $meterReading): RedirectResponse
    {
        // Check if reading can be deleted
        if (! $meterReading->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This reading has already been billed and cannot be deleted.');
        }

        $period = $meterReading->billing_period;
        $meterReading->delete();

        return redirect()->route('meter-readings.index', ['period' => $period])
            ->with('success', 'Reading deleted successfully.');
    }

    /**
     * Get previous reading for a consumer via AJAX.
     */
    public function getPreviousReading(Consumer $consumer): \Illuminate\Http\JsonResponse
    {
        $previousReading = MeterReading::getPreviousReading($consumer->id);

        return response()->json([
            'previous_reading' => $previousReading,
            'consumer_name' => $consumer->user->full_name ?? 'N/A',
            'consumer_address' => $consumer->address ?? 'N/A',
        ]);
    }
}
