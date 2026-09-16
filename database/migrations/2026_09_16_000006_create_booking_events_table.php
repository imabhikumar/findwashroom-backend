<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->json('event_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('booking_events'); }
};