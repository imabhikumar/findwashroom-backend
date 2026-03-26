<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->string('proof_image_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->dropColumn('proof_image_path');
        });
    }
};
