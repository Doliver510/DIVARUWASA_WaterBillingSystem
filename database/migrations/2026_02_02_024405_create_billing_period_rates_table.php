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
        Schema::create('billing_period_rates', function (Blueprint $table) {
            $table->id();
            $table->string('billing_period', 7)->unique(); // e.g., "2026-01"
            $table->decimal('base_charge', 10, 2);
            $table->integer('base_charge_covers_cubic');
            $table->decimal('penalty_fee', 10, 2);
            $table->integer('grace_period_days');
            $table->json('rate_brackets'); // Snapshot of all brackets
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_period_rates');
    }
};
