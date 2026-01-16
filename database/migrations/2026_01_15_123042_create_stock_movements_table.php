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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Who made the change
            $table->enum('type', ['in', 'out', 'adjustment']); // Type of movement
            $table->integer('quantity'); // Positive for in, negative for out
            $table->integer('stock_before'); // Stock level before this movement
            $table->integer('stock_after'); // Stock level after this movement
            $table->string('reference_type')->nullable(); // e.g., 'maintenance_request', 'manual_adjustment'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['material_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
