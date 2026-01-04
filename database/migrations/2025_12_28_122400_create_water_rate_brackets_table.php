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
        Schema::create('water_rate_brackets', function (Blueprint $table) {
            $table->id();
            $table->integer('min_cubic')->default(0);
            $table->integer('max_cubic')->nullable(); // NULL = unlimited
            $table->decimal('rate_per_cubic', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_rate_brackets');
    }
};
