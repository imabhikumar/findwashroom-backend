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
        Schema::create('properties', function (Blueprint $table) {
        $table->id();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->text('address');
        $table->string('city')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->decimal('price_per_use', 8, 2)->default(0);
        $table->decimal('average_rating', 3, 2)->default(0);
        $table->integer('total_reviews')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
