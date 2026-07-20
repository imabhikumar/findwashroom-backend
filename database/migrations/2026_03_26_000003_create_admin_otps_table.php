<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_otps', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 10); // sms | email
            // '191' keeps composite index under older MySQL limits with utf8mb4.
            $table->string('identifier', '191'); // mobile digits or email
            $table->string('otp_hash');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_otps');
    }
};

