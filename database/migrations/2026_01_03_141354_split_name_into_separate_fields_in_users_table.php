<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the name column still exists (this migration may have already run)
        if (!Schema::hasColumn('users', 'name')) {
            // Migration already completed, the new columns should exist
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('middle_name')->after('first_name')->nullable();
            $table->string('last_name')->after('middle_name')->nullable();
        });

        // Use database-agnostic migration for data (works with SQLite for testing)
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite (testing), use PHP-based migration
            $users = DB::table('users')->whereNotNull('name')->get();
            foreach ($users as $user) {
                $parts = explode(' ', $user->name);
                $firstName = $parts[0] ?? '';
                $lastName = count($parts) > 1 ? end($parts) : $firstName;
                $middleName = count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : null;

                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                ]);
            }
        } else {
            // For MySQL (production), use MySQL-specific functions for performance
            DB::statement("
                UPDATE users SET
                    last_name = SUBSTRING_INDEX(name, ' ', -1),
                    first_name = CASE
                        WHEN LOCATE(' ', name) > 0 THEN SUBSTRING_INDEX(name, ' ', 1)
                        ELSE name
                    END,
                    middle_name = CASE
                        WHEN LENGTH(name) - LENGTH(REPLACE(name, ' ', '')) >= 2
                        THEN TRIM(SUBSTRING(name, LOCATE(' ', name) + 1, LENGTH(name) - LOCATE(' ', name) - LENGTH(SUBSTRING_INDEX(name, ' ', -1)) - 1))
                        ELSE NULL
                    END
            ");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the name column already exists
        if (Schema::hasColumn('users', 'name')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite (testing), use PHP-based migration
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $name = trim($user->first_name . ' ' . ($user->middle_name ?? '') . ' ' . $user->last_name);
                DB::table('users')->where('id', $user->id)->update(['name' => $name]);
            }
        } else {
            // For MySQL (production)
            DB::statement("
                UPDATE users SET name = CONCAT(
                    first_name,
                    CASE WHEN middle_name IS NOT NULL AND middle_name != '' THEN CONCAT(' ', middle_name) ELSE '' END,
                    ' ',
                    last_name
                )
            ");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
