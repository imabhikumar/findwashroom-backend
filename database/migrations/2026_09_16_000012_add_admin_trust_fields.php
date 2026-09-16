<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            if (! Schema::hasColumn('user_badges', 'awarded_by')) $table->unsignedBigInteger('awarded_by')->nullable();
        });
        Schema::table('property_badges', function (Blueprint $table) {
            if (! Schema::hasColumn('property_badges', 'awarded_by')) $table->unsignedBigInteger('awarded_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_badges', function (Blueprint $table) { if (Schema::hasColumn('user_badges', 'awarded_by')) $table->dropColumn('awarded_by'); });
        Schema::table('property_badges', function (Blueprint $table) { if (Schema::hasColumn('property_badges', 'awarded_by')) $table->dropColumn('awarded_by'); });
    }
};