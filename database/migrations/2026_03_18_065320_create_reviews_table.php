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
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
        $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
        $table->integer('rating');
        $table->text('comment')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
