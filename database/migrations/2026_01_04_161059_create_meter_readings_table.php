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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained()->onDelete('cascade');
            $table->integer('reading_value'); // Current meter reading in cu.m
            $table->integer('previous_reading')->default(0); // Previous reading
            $table->integer('consumption')->default(0); // Calculated: reading_value - previous_reading
            $table->date('reading_date'); // Date reading was taken
            $table->string('billing_period', 7); // Format: YYYY-MM (e.g., "2026-01")
            $table->foreignId('read_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_billed')->default(false); // Locked once bill is generated
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Prevent duplicate readings for same consumer and billing period
            $table->unique(['consumer_id', 'billing_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
