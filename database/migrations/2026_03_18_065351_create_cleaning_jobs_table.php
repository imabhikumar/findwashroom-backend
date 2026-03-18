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
    Schema::create('cleaning_jobs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
        $table->decimal('price_offer', 8, 2);
        $table->foreignId('assigned_cleaner_id')->nullable()->constrained('users')->nullOnDelete();
        $table->enum('status', ['open', 'assigned', 'completed', 'cancelled'])->default('open');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_jobs');
    }
};
