<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
        $table->string('payment_gateway')->nullable();
        $table->string('transaction_id')->nullable();
        $table->decimal('amount', 8, 2);
        $table->decimal('platform_commission', 8, 2)->default(0);
        $table->decimal('owner_amount', 8, 2)->default(0);
        $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
        $table->timestamps();
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
