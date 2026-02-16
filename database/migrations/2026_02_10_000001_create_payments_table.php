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
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('amount')->comment('Amount in IDR (lowest denomination)');
            $table->string('currency')->default('IDR');
            $table->string('payment_method')->nullable()->comment('VIRTUAL_ACCOUNT, QRIS, E_WALLET, BANK_TRANSFER, etc');
            $table->string('payment_gateway')->comment('doku, stripe, paypal, etc');
            $table->string('external_id')->unique()->nullable()->comment('Payment gateway transaction ID');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired', 'cancelled', 'refunded'])->default('pending');
            $table->string('reference_number')->unique()->nullable()->comment('Our payment reference number');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional data from payment gateway');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('external_id');
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
