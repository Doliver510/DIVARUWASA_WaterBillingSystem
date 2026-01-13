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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('or_number')->unique(); // Official Receipt Number
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consumer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete(); // Cashier/Admin who processed
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2); // Balance before this payment
            $table->decimal('balance_after', 10, 2);  // Balance after this payment
            $table->string('payment_method')->default('cash'); // For future expansion, currently only 'cash'
            $table->text('remarks')->nullable();
            $table->timestamp('paid_at'); // When the payment was made
            $table->timestamps();

            $table->index('or_number');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
