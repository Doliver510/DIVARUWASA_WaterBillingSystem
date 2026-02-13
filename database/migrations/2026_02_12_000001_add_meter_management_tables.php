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
        // 1. Add meter_number to consumers table
        Schema::table('consumers', function (Blueprint $table) {
            $table->string('meter_number', 50)->nullable()->after('lot_number');
        });

        // 2. Create consumer_meters table for meter history/replacement tracking
        Schema::create('consumer_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained()->cascadeOnDelete();
            $table->string('meter_number', 50);
            $table->date('installed_at');
            $table->date('removed_at')->nullable();
            $table->string('removal_reason', 100)->nullable(); // "replacement", "defective", etc.
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('meter_cost', 10, 2)->nullable();
            $table->unsignedTinyInteger('installment_months')->default(4);
            $table->unsignedTinyInteger('installments_billed')->default(0);
            $table->decimal('installment_amount', 10, 2)->nullable(); // per-month amount
            $table->decimal('total_paid', 10, 2)->default(0); // total paid toward meter
            $table->boolean('fully_paid')->default(false);
            $table->timestamps();
        });

        // 3. Add meter_installment to bills table
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('meter_installment', 10, 2)->default(0)->after('other_charges');
        });

        // 4. Add new_meter_number to maintenance_requests table
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('new_meter_number', 50)->nullable()->after('payment_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('new_meter_number');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('meter_installment');
        });

        Schema::dropIfExists('consumer_meters');

        Schema::table('consumers', function (Blueprint $table) {
            $table->dropColumn('meter_number');
        });
    }
};
