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
    Schema::create('complaints', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
        $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
        $table->text('description');
        $table->string('evidence_image_path')->nullable();
        $table->enum('status', ['pending', 'resolved', 'rejected'])->default('pending');
        $table->text('admin_note')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
