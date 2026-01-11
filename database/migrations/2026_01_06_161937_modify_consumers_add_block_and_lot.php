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
            // Add new columns
            $table->foreignId('block_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->unsignedInteger('lot_number')->nullable()->after('block_id');

            // Remove old address column
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumers', function (Blueprint $table) {
            // Restore address column
            $table->string('address')->nullable()->after('user_id');

            // Remove new columns
            $table->dropForeign(['block_id']);
            $table->dropColumn(['block_id', 'lot_number']);
        });
    }
};
