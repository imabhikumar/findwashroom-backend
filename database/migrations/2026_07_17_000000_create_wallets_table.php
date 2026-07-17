// database/migrations/2026_07_17_000000_create_wallets_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('wallet_type')->default('customer'); // customer, owner, cleaner
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['active', 'frozen', 'closed'])->default('active');
            $table->timestamps();
            
            $table->unique(['user_id', 'wallet_type']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type',30); // credit, debit, refund, commission, adjustment, penalty
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_type')->nullable(); // booking, payment, payout, deposit
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status',20)->default('completed'); // pending, completed, failed, reversed
            $table->timestamps();
            
            $table->index(['wallet_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['transaction_type', 'status']);
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'held', 'released', 'forfeited'])->default('pending');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->index(['booking_id', 'status']);
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payout_method'); // bank_transfer, upi, razorpay
            $table->string('account_details')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'reversed'])->default('pending');
            $table->string('reference_id')->nullable(); // gateway reference
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
