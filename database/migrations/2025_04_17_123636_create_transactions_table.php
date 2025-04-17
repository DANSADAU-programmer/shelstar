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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('payable'); // Link to order/booking
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_reference')->nullable()->unique(); // Your app's unique reference
            $table->string('gateway_reference')->nullable(); // Payment gateway's transaction ID
            $table->string('payment_gateway'); // e.g., 'paystack', 'flutterwave'
            $table->string('payment_method')->nullable(); // e.g., 'card', 'banktransfer'
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('NGN');
            $table->string('status')->default('pending'); // pending, successful, failed
            $table->json('gateway_response')->nullable(); // Raw response (sensitive data should be handled carefully)
            $table->timestamp('paid_at')->nullable(); // When payment was confirmed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
