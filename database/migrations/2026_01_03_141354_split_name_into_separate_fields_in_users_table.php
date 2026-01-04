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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('middle_name')->after('first_name')->nullable();
            $table->string('last_name')->after('middle_name')->nullable();
        });

        // Migrate existing data: assume last word is surname, first word is first name
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        // Reconstruct full name
        DB::statement("
            UPDATE users SET name = CONCAT(
                first_name,
                CASE WHEN middle_name IS NOT NULL AND middle_name != '' THEN CONCAT(' ', middle_name) ELSE '' END,
                ' ',
                last_name
            )
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
