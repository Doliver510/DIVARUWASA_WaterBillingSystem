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
        Schema::table('consumers', function (Blueprint $table) {
            // Remove meter_serial_no as we use account_no as identifier
            $table->dropColumn('meter_serial_no');

            // Rename account_no to id_no for clarity
            $table->renameColumn('account_no', 'id_no');
        });

        // Change id_no to be numeric string (3 digits, zero-padded)
        // We keep it as string to preserve leading zeros like "001", "014"
        Schema::table('consumers', function (Blueprint $table) {
            $table->string('id_no', 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumers', function (Blueprint $table) {
            $table->renameColumn('id_no', 'account_no');
            $table->string('meter_serial_no', 50)->nullable()->after('account_no');
        });
    }
};
