<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained()->onDelete('cascade');
            $table->foreignId('meter_reading_id')->nullable()->constrained()->onDelete('set null');

            // Billing Period
            $table->string('billing_period', 7); // Format: YYYY-MM
            $table->date('period_from');
            $table->date('period_to');

            // Reading Info
            $table->integer('previous_reading')->default(0);
            $table->integer('present_reading')->default(0);
            $table->integer('consumption')->default(0);

            // Charges Breakdown
            $table->decimal('water_charge', 10, 2)->default(0); // Base + tiered rates
            $table->decimal('arrears', 10, 2)->default(0); // Previous unpaid balance
            $table->decimal('penalty', 10, 2)->default(0); // Late payment penalty
            $table->decimal('other_charges', 10, 2)->default(0); // Material charges, etc.
            $table->decimal('total_amount', 10, 2)->default(0); // Sum of all charges

            // Payment Tracking
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0); // total_amount - amount_paid

            // Important Dates
            $table->date('disconnection_date'); // End of month (pay before this without penalty)
            $table->date('due_date_start'); // Grace period start (Day 1 of next month)
            $table->date('due_date_end'); // Grace period end (Day 5 of next month)

            // Status
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');

            $table->text('remarks')->nullable();
            $table->timestamps();

            // Unique constraint: one bill per consumer per billing period
            $table->unique(['consumer_id', 'billing_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
