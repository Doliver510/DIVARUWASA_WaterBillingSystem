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
        Schema::table('payments', function (Blueprint $table) {
            // Make bill_id nullable (for material payments that don't have a bill)
            $table->foreignId('bill_id')->nullable()->change();

            // Add payment type to distinguish bill vs maintenance payments
            $table->enum('payment_type', ['bill', 'maintenance'])->default('bill')->after('or_number');

            // Add maintenance_request_id for material payments
            $table->foreignId('maintenance_request_id')->nullable()->after('bill_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['maintenance_request_id']);
            $table->dropColumn(['payment_type', 'maintenance_request_id']);

            // Note: Can't easily revert bill_id to non-nullable if data exists
        });
    }
};
