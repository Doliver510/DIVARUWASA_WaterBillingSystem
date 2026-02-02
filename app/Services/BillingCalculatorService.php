<?php

namespace App\Services;

use App\Mail\BillGeneratedMail;
use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\Consumer;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\WaterRateBracket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BillingCalculatorService
{
    /**
     * Generate a bill for a meter reading.
     */
    public function generateBillFromReading(MeterReading $reading): Bill
    {
        return DB::transaction(function () use ($reading) {
            // Check if bill already exists for this reading
            $existingBill = Bill::where('meter_reading_id', $reading->id)->first();
            if ($existingBill) {
                return $this->regenerateBill($existingBill, $reading);
            }

            // Calculate dates
            $dates = $this->calculateBillingDates($reading->billing_period);

            // Calculate water charge using locked rates for this billing period
            // This ensures all bills in the same period use the same rates
            $waterCharge = \App\Models\BillingPeriodRate::calculateChargeForPeriod(
                $reading->consumption, 
                $reading->billing_period
            );

            // Get arrears (previous unpaid balances)
            $arrears = Bill::getArrears($reading->consumer_id, $reading->billing_period);

            // Get pending material charges (charge_to_bill)
            $otherCharges = $this->getPendingMaterialCharges($reading->consumer_id);

            // Check if penalty should be applied (from previous overdue bills)
            $penalty = $this->calculatePenalty($reading->consumer_id);

            // Calculate totals
            $totalAmount = $waterCharge + $arrears + $penalty + $otherCharges;
            $balance = $totalAmount;

            // Create the bill
            $bill = Bill::create([
                'consumer_id' => $reading->consumer_id,
                'meter_reading_id' => $reading->id,
                'billing_period' => $reading->billing_period,
                'period_from' => $dates['period_from'],
                'period_to' => $dates['period_to'],
                'previous_reading' => $reading->previous_reading,
                'present_reading' => $reading->reading_value,
                'consumption' => $reading->consumption,
                'water_charge' => $waterCharge,
                'arrears' => $arrears,
                'penalty' => $penalty,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance' => $balance,
                'disconnection_date' => $dates['disconnection_date'],
                'due_date_start' => $dates['due_date_start'],
                'due_date_end' => $dates['due_date_end'],
                'status' => 'unpaid',
            ]);

            // Mark the meter reading as billed
            $reading->is_billed = true;
            $reading->save();

            // Mark material charges as billed
            $this->markMaterialChargesAsBilled($reading->consumer_id, $bill->id);

            // Send bill email to consumer
            $this->sendBillEmail($bill);

            return $bill;
        });
    }

    /**
     * Regenerate a bill (when reading is updated).
     */
    public function regenerateBill(Bill $bill, MeterReading $reading): Bill
    {
        // Recalculate water charge
        $waterCharge = WaterRateBracket::calculateCharge($reading->consumption);

        // Keep existing arrears, penalty, other_charges
        $totalAmount = $waterCharge + $bill->arrears + $bill->penalty + $bill->other_charges;
        $balance = $totalAmount - $bill->amount_paid;

        $bill->update([
            'previous_reading' => $reading->previous_reading,
            'present_reading' => $reading->reading_value,
            'consumption' => $reading->consumption,
            'water_charge' => $waterCharge,
            'total_amount' => $totalAmount,
            'balance' => $balance,
        ]);

        $bill->updateStatus();

        return $bill;
    }

    /**
     * Calculate billing dates based on billing period.
     */
    public function calculateBillingDates(string $billingPeriod): array
    {
        $cycleStartDay = (int) AppSetting::getValue('billing_cycle_start_day', 10);
        $gracePeriodDays = (int) AppSetting::getValue('grace_period_days', 5);

        // Parse billing period (e.g., "2025-12" for December 2025)
        $periodDate = Carbon::createFromFormat('Y-m', $billingPeriod);

        // Period From: 10th of previous month
        $periodFrom = $periodDate->copy()->subMonth()->day($cycleStartDay);

        // Period To: 10th of billing period month
        $periodTo = $periodDate->copy()->day($cycleStartDay);

        // Disconnection Date: End of billing period month
        $disconnectionDate = $periodDate->copy()->endOfMonth();

        // Due Date Start: 1st of next month
        $dueDateStart = $periodDate->copy()->addMonth()->startOfMonth();

        // Due Date End: Grace period days after start
        $dueDateEnd = $dueDateStart->copy()->addDays($gracePeriodDays - 1);

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'disconnection_date' => $disconnectionDate,
            'due_date_start' => $dueDateStart,
            'due_date_end' => $dueDateEnd,
        ];
    }

    /**
     * Get pending material charges for a consumer.
     */
    public function getPendingMaterialCharges(int $consumerId): float
    {
        return (float) MaintenanceRequest::where('consumer_id', $consumerId)
            ->where('status', 'completed')
            ->where('payment_option', 'charge_to_bill')
            ->whereNull('billed_at')
            ->sum('total_material_cost');
    }

    /**
     * Mark material charges as billed.
     */
    public function markMaterialChargesAsBilled(int $consumerId, int $billId): void
    {
        $requests = MaintenanceRequest::where('consumer_id', $consumerId)
            ->where('status', 'completed')
            ->where('payment_option', 'charge_to_bill')
            ->whereNull('billed_at')
            ->get();

        foreach ($requests as $request) {
            $request->update([
                'billed_at' => now(),
                'remarks' => ($request->remarks ?? '') . " [Billed: Bill #{$billId}]",
            ]);
        }
    }

    /**
     * Calculate penalty for previous overdue bills.
     */
    public function calculatePenalty(int $consumerId): float
    {
        // Check if there are any overdue bills
        $hasOverdue = Bill::where('consumer_id', $consumerId)
            ->where('status', 'overdue')
            ->exists();

        if ($hasOverdue) {
            return (float) AppSetting::getValue('penalty_fee', 50);
        }

        return 0;
    }

    /**
     * Get charge breakdown for display on bills.
     */
    public function getChargeBreakdown(int $consumption): array
    {
        return WaterRateBracket::getChargeBreakdown($consumption);
    }

    /**
     * Send bill email to consumer.
     */
    private function sendBillEmail(Bill $bill): void
    {
        // Load consumer with user relationship
        $bill->loadMissing('consumer.user');

        $email = $bill->consumer->user->email ?? null;

        if (empty($email)) {
            return;
        }

        try {
            Mail::to($email)->send(new BillGeneratedMail($bill));
        } catch (\Exception $e) {
            \Log::error('Failed to send bill email to '.$email.': '.$e->getMessage());
        }
    }
}
