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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_id')->unique()->nullable(); // FIB payment ID
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IQD');
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_url')->nullable();
            $table->text('qr_code')->nullable(); // Base64 encoded QR code
            $table->string('callback_url')->nullable();
            $table->text('fib_response')->nullable(); // Store FIB API response
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('payment_id');
            $table->index('status');
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
