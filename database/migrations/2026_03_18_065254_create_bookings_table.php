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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
        $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
        $table->timestamp('start_time')->nullable();
        $table->timestamp('end_time')->nullable();
        $table->decimal('amount', 8, 2)->default(0);
        $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
        $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
