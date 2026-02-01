<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add password_changed_at column to track if user has changed their default password.
     * Null = password not changed yet (force change on first login)
     * Timestamped = password has been changed by user
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });

        // Set password_changed_at for existing users (so they don't get forced to change)
        // Only new consumers created after this migration will have null password_changed_at
        DB::table('users')->whereNull('password_changed_at')->update([
            'password_changed_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};
