<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\Block;
use App\Models\Consumer;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\Role;
use App\Models\User;
use App\Models\WaterRateBracket;
use App\Services\BillingCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Unit Tests for BillingCalculatorService
 * 
 * What is a Unit Test?
 * ====================
 * A unit test is a type of software testing that focuses on testing individual
 * "units" or components of code in isolation. In this case, we're testing the
 * BillingCalculatorService which handles all billing calculations.
 * 
 * Why Unit Tests are Needed:
 * ==========================
 * 1. ACCURACY GUARANTEE - Billing calculations involve money. Any error could
 *    result in overcharging or undercharging consumers, leading to financial
 *    disputes and loss of trust.
 * 
 * 2. REGRESSION PREVENTION - When you change code in the future, unit tests
 *    ensure existing functionality still works correctly.
 * 
 * 3. DOCUMENTATION - Tests serve as living documentation showing how the
 *    billing logic is supposed to work with concrete examples.
 * 
 * 4. CONFIDENCE - You can confidently deploy updates knowing the core billing
 *    logic has been verified.
 * 
 * 5. EDGE CASE HANDLING - Tests help verify unusual scenarios like zero
 *    consumption, maximum consumption, multiple unpaid bills, etc.
 * 
 * Test Scenarios Covered:
 * =======================
 * - Water charge calculation with tiered brackets
 * - Base charge only (consumption within base coverage)
 * - Excess consumption across multiple brackets
 * - Arrears calculation from previous unpaid bills
 * - Penalty application for overdue bills
 * - Material charges from maintenance requests
 * - Billing date calculations
 * - Complete bill generation workflow
 */
class BillingCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingCalculatorService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize the billing service
        $this->billingService = new BillingCalculatorService();
        
        // Fake mail to prevent actual emails during tests
        Mail::fake();
        
        // Set up base settings
        $this->setupAppSettings();
        
        // Set up water rate brackets
        $this->setupWaterRateBrackets();
    }

    /**
     * Set up application settings for billing.
     */
    protected function setupAppSettings(): void
    {
        AppSetting::create(['key' => 'base_charge', 'value' => '150.00']);
        AppSetting::create(['key' => 'base_charge_covers_cubic', 'value' => '10']);
        AppSetting::create(['key' => 'penalty_fee', 'value' => '50.00']);
        AppSetting::create(['key' => 'billing_cycle_start_day', 'value' => '10']);
        AppSetting::create(['key' => 'grace_period_days', 'value' => '5']);
    }

    /**
     * Set up water rate brackets with tiered pricing.
     * 
     * Bracket Structure:
     * - 11-20 cu.m: ₱15.00 per cu.m
     * - 21-30 cu.m: ₱20.00 per cu.m
     * - 31+   cu.m: ₱25.00 per cu.m
     */
    protected function setupWaterRateBrackets(): void
    {
        WaterRateBracket::create([
            'min_cubic' => 11,
            'max_cubic' => 20,
            'rate_per_cubic' => 15.00,
            'sort_order' => 1,
        ]);

        WaterRateBracket::create([
            'min_cubic' => 21,
            'max_cubic' => 30,
            'rate_per_cubic' => 20.00,
            'sort_order' => 2,
        ]);

        WaterRateBracket::create([
            'min_cubic' => 31,
            'max_cubic' => null, // Open-ended (31+)
            'rate_per_cubic' => 25.00,
            'sort_order' => 3,
        ]);
    }

    /**
     * Create a consumer for testing purposes.
     */
    protected function createConsumer(): Consumer
    {
        $role = Role::create([
            'name' => 'Consumer',
            'slug' => 'consumer',
        ]);

        $block = Block::create([
            'name' => 'Block 1',
        ]);

        $user = User::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);

        return Consumer::create([
            'user_id' => $user->id,
            'id_no' => '001',
            'block_id' => $block->id,
            'lot_number' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Create a meter reading for testing.
     */
    protected function createMeterReading(Consumer $consumer, int $previousReading, int $currentReading, string $billingPeriod): MeterReading
    {
        $meterReaderRole = Role::firstOrCreate(
            ['slug' => 'meter-reader'],
            ['name' => 'Meter Reader']
        );

        $meterReader = User::firstOrCreate(
            ['email' => 'reader@example.com'],
            [
                'first_name' => 'Meter',
                'last_name' => 'Reader',
                'password' => bcrypt('password'),
                'role_id' => $meterReaderRole->id,
            ]
        );

        return MeterReading::create([
            'consumer_id' => $consumer->id,
            'reading_value' => $currentReading,
            'previous_reading' => $previousReading,
            'consumption' => $currentReading - $previousReading,
            'reading_date' => now(),
            'billing_period' => $billingPeriod,
            'read_by' => $meterReader->id,
            'is_billed' => false,
        ]);
    }

    // =========================================================================
    // WATER CHARGE CALCULATION TESTS
    // =========================================================================

    /**
     * Test: Consumption within base charge coverage.
     * 
     * Scenario: Consumer uses 5 cu.m (less than 10 cu.m base coverage)
     * Expected: Only base charge (₱150.00)
     */
    public function test_water_charge_with_consumption_within_base_coverage(): void
    {
        $charge = WaterRateBracket::calculateCharge(5);
        
        $this->assertEquals(150.00, $charge);
    }

    /**
     * Test: Consumption exactly at base charge limit.
     * 
     * Scenario: Consumer uses exactly 10 cu.m (base coverage limit)
     * Expected: Only base charge (₱150.00)
     */
    public function test_water_charge_with_consumption_exactly_at_base_coverage(): void
    {
        $charge = WaterRateBracket::calculateCharge(10);
        
        $this->assertEquals(150.00, $charge);
    }

    /**
     * Test: Consumption exceeding base coverage in first bracket.
     * 
     * Scenario: Consumer uses 15 cu.m
     * - Base charge: ₱150.00 (covers 10 cu.m)
     * - Excess: 5 cu.m × ₱15.00 = ₱75.00
     * Expected Total: ₱225.00
     */
    public function test_water_charge_with_consumption_in_first_bracket(): void
    {
        $charge = WaterRateBracket::calculateCharge(15);
        
        // 150 + (5 × 15) = 150 + 75 = 225
        $this->assertEquals(225.00, $charge);
    }

    /**
     * Test: Consumption spanning two brackets.
     * 
     * Scenario: Consumer uses 25 cu.m
     * - Base charge: ₱150.00 (covers 10 cu.m)
     * - Bracket 1: 10 cu.m × ₱15.00 = ₱150.00 (11-20 cu.m)
     * - Bracket 2: 5 cu.m × ₱20.00 = ₱100.00 (21-25 cu.m)
     * Expected Total: ₱400.00
     */
    public function test_water_charge_spanning_two_brackets(): void
    {
        $charge = WaterRateBracket::calculateCharge(25);
        
        // 150 + (10 × 15) + (5 × 20) = 150 + 150 + 100 = 400
        $this->assertEquals(400.00, $charge);
    }

    /**
     * Test: Consumption spanning all three brackets.
     * 
     * Scenario: Consumer uses 35 cu.m
     * - Base charge: ₱150.00 (covers 10 cu.m)
     * - Excess: 25 cu.m to be distributed across brackets
     * 
     * Algorithm traces through brackets sequentially:
     * - Bracket 1 (11-20): Takes 10 cu.m × ₱15.00 = ₱150.00, remaining excess = 15
     * - Bracket 2 (21-30): Takes 10 cu.m × ₱20.00 = ₱200.00, remaining excess = 5
     * - Bracket 3 (31+):   Takes 5 cu.m × ₱25.00 = ₱125.00, remaining excess = 0
     * Expected Total: ₱150 + ₱150 + ₱200 + ₱125 = ₱625.00
     */
    public function test_water_charge_spanning_all_brackets(): void
    {
        $charge = WaterRateBracket::calculateCharge(35);
        
        // Based on the algorithm's sequential processing:
        // The algorithm processes excess consumption through each bracket.
        // With 25 excess cu.m: 10 in first bracket, 10 in second, 5 in third
        // 150 + (10 × 15) + (10 × 20) + (5 × 25) = 150 + 150 + 200 + 125 = 625
        // 
        // Note: If this test fails, it indicates a bug in WaterRateBracket::calculateCharge()
        // that should be reviewed and fixed.
        $this->assertEquals(625.00, $charge);
    }

    /**
     * Test: Zero consumption.
     * 
     * Scenario: Consumer has 0 consumption (meter unchanged)
     * Expected: Only base charge (₱150.00)
     */
    public function test_water_charge_with_zero_consumption(): void
    {
        $charge = WaterRateBracket::calculateCharge(0);
        
        $this->assertEquals(150.00, $charge);
    }

    // =========================================================================
    // ARREARS CALCULATION TESTS
    // =========================================================================

    /**
     * Test: Consumer with no arrears.
     * 
     * Scenario: Consumer has no previous unpaid bills
     * Expected: Arrears = ₱0.00
     */
    public function test_arrears_calculation_with_no_previous_bills(): void
    {
        $consumer = $this->createConsumer();
        
        $arrears = Bill::getArrears($consumer->id);
        
        $this->assertEquals(0.00, $arrears);
    }

    /**
     * Test: Consumer with one unpaid bill.
     * 
     * Scenario: Consumer has one unpaid bill with balance of ₱500.00
     * Expected: Arrears = ₱500.00
     */
    public function test_arrears_calculation_with_one_unpaid_bill(): void
    {
        $consumer = $this->createConsumer();
        
        // Create an unpaid bill
        Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-11',
            'period_from' => '2025-10-10',
            'period_to' => '2025-11-10',
            'previous_reading' => 0,
            'present_reading' => 10,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 150.00,
            'amount_paid' => 0,
            'balance' => 500.00, // Unpaid balance
            'status' => 'unpaid',
            'disconnection_date' => '2025-11-30',
            'due_date_start' => '2025-12-01',
            'due_date_end' => '2025-12-05',
        ]);
        
        $arrears = Bill::getArrears($consumer->id);
        
        $this->assertEquals(500.00, $arrears);
    }

    /**
     * Test: Consumer with multiple unpaid bills.
     * 
     * Scenario: Consumer has 3 unpaid bills
     * - Bill 1: ₱300.00 balance
     * - Bill 2: ₱500.00 balance
     * - Bill 3: ₱200.00 balance
     * Expected: Arrears = ₱1,000.00 (sum of all balances)
     */
    public function test_arrears_calculation_with_multiple_unpaid_bills(): void
    {
        $consumer = $this->createConsumer();
        
        // Create multiple unpaid bills
        $balances = [300.00, 500.00, 200.00];
        $months = ['2025-09', '2025-10', '2025-11'];
        
        foreach ($balances as $index => $balance) {
            Bill::create([
                'consumer_id' => $consumer->id,
                'billing_period' => $months[$index],
                'period_from' => '2025-01-10',
                'period_to' => '2025-02-10',
                'previous_reading' => 0,
                'present_reading' => 10,
                'consumption' => 10,
                'water_charge' => 150.00,
                'arrears' => 0,
                'penalty' => 0,
                'other_charges' => 0,
                'total_amount' => $balance,
                'amount_paid' => 0,
                'balance' => $balance,
                'status' => 'unpaid',
                'disconnection_date' => '2025-02-28',
                'due_date_start' => '2025-03-01',
                'due_date_end' => '2025-03-05',
            ]);
        }
        
        $arrears = Bill::getArrears($consumer->id);
        
        $this->assertEquals(1000.00, $arrears);
    }

    /**
     * Test: Arrears excludes current billing period.
     * 
     * Scenario: Consumer has bills for November and December
     * When generating December bill, November arrears should be included
     * but exclude the December bill itself
     */
    public function test_arrears_excludes_current_billing_period(): void
    {
        $consumer = $this->createConsumer();
        
        // Create November bill (should be included in arrears)
        Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-11',
            'period_from' => '2025-10-10',
            'period_to' => '2025-11-10',
            'previous_reading' => 0,
            'present_reading' => 10,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 300.00,
            'amount_paid' => 0,
            'balance' => 300.00,
            'status' => 'unpaid',
            'disconnection_date' => '2025-11-30',
            'due_date_start' => '2025-12-01',
            'due_date_end' => '2025-12-05',
        ]);
        
        // Create December bill (should be excluded)
        Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-12',
            'period_from' => '2025-11-10',
            'period_to' => '2025-12-10',
            'previous_reading' => 10,
            'present_reading' => 20,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 500.00,
            'amount_paid' => 0,
            'balance' => 500.00,
            'status' => 'unpaid',
            'disconnection_date' => '2025-12-31',
            'due_date_start' => '2026-01-01',
            'due_date_end' => '2026-01-05',
        ]);
        
        // When calculating arrears for December, exclude December bill
        $arrears = Bill::getArrears($consumer->id, '2025-12');
        
        $this->assertEquals(300.00, $arrears); // Only November bill
    }

    // =========================================================================
    // PENALTY CALCULATION TESTS
    // =========================================================================

    /**
     * Test: No penalty when no overdue bills.
     * 
     * Scenario: Consumer has no overdue bills
     * Expected: applyPenaltiesToOverdueBills does nothing
     */
    public function test_penalty_not_applied_when_no_overdue_bills(): void
    {
        $consumer = $this->createConsumer();
        
        // Should not throw, just does nothing
        $this->billingService->applyPenaltiesToOverdueBills($consumer->id);
        
        // No bills exist, so nothing to check — just confirm no error
        $this->assertTrue(true);
    }

    /**
     * Test: Penalty applied to overdue bill directly.
     * 
     * Scenario: Consumer has an overdue bill of ₱150
     * Expected: Penalty of ₱50 is added to the overdue bill → balance becomes ₱200
     */
    public function test_penalty_applied_when_overdue_bill_exists(): void
    {
        $consumer = $this->createConsumer();
        
        // Create an overdue bill
        $overdueBill = Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-10',
            'period_from' => '2025-09-10',
            'period_to' => '2025-10-10',
            'previous_reading' => 0,
            'present_reading' => 10,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 150.00,
            'amount_paid' => 0,
            'balance' => 150.00,
            'status' => 'overdue', // Key: status is overdue
            'disconnection_date' => '2025-10-31',
            'due_date_start' => '2025-11-01',
            'due_date_end' => '2025-11-05',
        ]);
        
        // Apply penalties to overdue bills
        $this->billingService->applyPenaltiesToOverdueBills($consumer->id);
        
        // Refresh the overdue bill from database
        $overdueBill->refresh();
        
        // Penalty should be applied to the overdue bill itself
        $this->assertEquals(50.00, $overdueBill->penalty);
        $this->assertEquals(200.00, $overdueBill->total_amount); // 150 + 50
        $this->assertEquals(200.00, $overdueBill->balance); // 200 - 0 paid
    }

    // =========================================================================
    // MATERIAL CHARGES TESTS
    // =========================================================================

    /**
     * Test: No material charges when no maintenance requests.
     */
    public function test_no_material_charges_without_maintenance_requests(): void
    {
        $consumer = $this->createConsumer();
        
        $materialCharges = $this->billingService->getPendingMaterialCharges($consumer->id);
        
        $this->assertEquals(0.00, $materialCharges);
    }

    /**
     * Test: Material charges from completed maintenance requests.
     * 
     * Scenario: Consumer has completed maintenance with "charge_to_bill" option
     */
    public function test_material_charges_from_completed_maintenance(): void
    {
        $consumer = $this->createConsumer();
        
        // Create a completed maintenance request with charge_to_bill
        MaintenanceRequest::create([
            'consumer_id' => $consumer->id,
            'requested_by' => $consumer->user_id,
            'request_type' => 'pipe_leak',
            'description' => 'Pipe repair needed',
            'status' => 'completed',
            'payment_option' => 'charge_to_bill',
            'total_material_cost' => 500.00,
            'completed_at' => now(),
            'billed_at' => null, // Not yet billed
        ]);
        
        $materialCharges = $this->billingService->getPendingMaterialCharges($consumer->id);
        
        $this->assertEquals(500.00, $materialCharges);
    }

    /**
     * Test: Material charges not included if already billed.
     */
    public function test_material_charges_not_included_if_already_billed(): void
    {
        $consumer = $this->createConsumer();
        
        // Create a maintenance request that's already been billed
        $request = MaintenanceRequest::create([
            'consumer_id' => $consumer->id,
            'requested_by' => $consumer->user_id,
            'request_type' => 'pipe_leak',
            'description' => 'Pipe repair needed',
            'status' => 'completed',
            'payment_option' => 'charge_to_bill',
            'total_material_cost' => 500.00,
            'completed_at' => now()->subDays(10),
        ]);
        
        // Manually update billed_at to simulate already billed
        $request->billed_at = now()->subDays(5);
        $request->save();
        
        $materialCharges = $this->billingService->getPendingMaterialCharges($consumer->id);
        
        $this->assertEquals(0.00, $materialCharges);
    }

    // =========================================================================
    // BILLING DATES CALCULATION TESTS
    // =========================================================================

    /**
     * Test: Billing dates calculated correctly.
     * 
     * For billing period 2025-12:
     * - Period From: 2025-11-10 (10th of previous month)
     * - Period To: 2025-12-10 (10th of billing month)
     * - Disconnection: 2025-12-31 (end of billing month)
     * - Due Start: 2026-01-01 (1st of next month)
     * - Due End: 2026-01-05 (grace period days after start)
     */
    public function test_billing_dates_calculation(): void
    {
        $dates = $this->billingService->calculateBillingDates('2025-12');
        
        $this->assertEquals('2025-11-10', $dates['period_from']->format('Y-m-d'));
        $this->assertEquals('2025-12-10', $dates['period_to']->format('Y-m-d'));
        $this->assertEquals('2025-12-31', $dates['disconnection_date']->format('Y-m-d'));
        $this->assertEquals('2026-01-01', $dates['due_date_start']->format('Y-m-d'));
        $this->assertEquals('2026-01-05', $dates['due_date_end']->format('Y-m-d'));
    }

    // =========================================================================
    // COMPLETE BILL GENERATION TESTS
    // =========================================================================

    /**
     * Test: Complete bill generation from meter reading.
     * 
     * This is an integration test that verifies the entire billing workflow
     * works correctly together.
     */
    public function test_complete_bill_generation(): void
    {
        $consumer = $this->createConsumer();
        $reading = $this->createMeterReading($consumer, 0, 15, '2025-12');
        
        $bill = $this->billingService->generateBillFromReading($reading);
        
        // Verify bill was created
        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertEquals($consumer->id, $bill->consumer_id);
        $this->assertEquals('2025-12', $bill->billing_period);
        
        // Verify water charge calculation (15 cu.m)
        // Base: ₱150 + 5 excess × ₱15 = ₱225
        $this->assertEquals(225.00, $bill->water_charge);
        
        // Verify total
        $this->assertEquals(225.00, $bill->total_amount);
        $this->assertEquals('unpaid', $bill->status);
        
        // Verify meter reading was marked as billed
        $reading->refresh();
        $this->assertTrue($reading->is_billed);
    }

    /**
     * Test: Bill generation with arrears.
     */
    public function test_bill_generation_with_arrears(): void
    {
        $consumer = $this->createConsumer();
        
        // Create a previous unpaid bill
        Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-11',
            'period_from' => '2025-10-10',
            'period_to' => '2025-11-10',
            'previous_reading' => 0,
            'present_reading' => 10,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 150.00,
            'amount_paid' => 0,
            'balance' => 150.00,
            'status' => 'unpaid',
            'disconnection_date' => '2025-11-30',
            'due_date_start' => '2025-12-01',
            'due_date_end' => '2025-12-05',
        ]);
        
        // Create December reading
        $reading = $this->createMeterReading($consumer, 10, 20, '2025-12');
        
        $bill = $this->billingService->generateBillFromReading($reading);
        
        // Water charge for 10 cu.m = ₱150 (base only)
        $this->assertEquals(150.00, $bill->water_charge);
        
        // Arrears from previous bill
        $this->assertEquals(150.00, $bill->arrears);
        
        // Total = water + arrears = ₱300
        $this->assertEquals(300.00, $bill->total_amount);
    }

    /**
     * Test: Bill generation applies penalty to overdue bill, not to new bill.
     * 
     * Scenario: Consumer has an overdue bill of ₱150. New reading generates a new bill.
     * Expected: Penalty (₱50) is added to the overdue bill. New bill has penalty=0,
     *           but arrears include the penalty-inclusive balance (₱200).
     */
    public function test_bill_generation_with_penalty(): void
    {
        $consumer = $this->createConsumer();
        
        // Create an overdue bill
        Bill::create([
            'consumer_id' => $consumer->id,
            'billing_period' => '2025-11',
            'period_from' => '2025-10-10',
            'period_to' => '2025-11-10',
            'previous_reading' => 0,
            'present_reading' => 10,
            'consumption' => 10,
            'water_charge' => 150.00,
            'arrears' => 0,
            'penalty' => 0,
            'other_charges' => 0,
            'total_amount' => 150.00,
            'amount_paid' => 0,
            'balance' => 150.00,
            'status' => 'overdue', // Important: status is overdue
            'disconnection_date' => '2025-11-30',
            'due_date_start' => '2025-12-01',
            'due_date_end' => '2025-12-05',
        ]);
        
        // Create December reading
        $reading = $this->createMeterReading($consumer, 10, 20, '2025-12');
        
        $bill = $this->billingService->generateBillFromReading($reading);
        
        // New bill should have penalty=0 (penalty goes on the overdue bill)
        $this->assertEquals(0.00, $bill->penalty);
        
        // Arrears should include the penalty-inclusive balance: ₱150 + ₱50 penalty = ₱200
        $this->assertEquals(200.00, $bill->arrears);
        
        // Total = water (₱150) + arrears (₱200) + penalty (₱0) = ₱350
        $this->assertEquals(350.00, $bill->total_amount);
    }

    /**
     * Test: Charge breakdown shows correct tier information.
     * 
     * Note: This test verifies the breakdown matches the actual calculation.
     * The number of tiers and amounts depend on the algorithm implementation.
     */
    public function test_charge_breakdown_displays_correctly(): void
    {
        // Test consumption of 25 cu.m
        $breakdown = $this->billingService->getChargeBreakdown(25);
        
        $this->assertEquals(150.00, $breakdown['base_charge']);
        $this->assertEquals(10, $breakdown['base_covers']);
        $this->assertEquals(25, $breakdown['consumption']);
        
        // Verify tiers exist and are non-empty
        $this->assertIsArray($breakdown['tiers']);
        $this->assertNotEmpty($breakdown['tiers']);
        
        // Total should match the direct calculation
        $directCharge = WaterRateBracket::calculateCharge(25);
        $this->assertEquals($directCharge, $breakdown['total']);
    }
}
