<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'owner_user_id')) {
                $table->unsignedBigInteger('owner_user_id')->nullable()->after('owner_id');
            }
            if (! Schema::hasColumn('properties', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (! Schema::hasColumn('properties', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            if (! Schema::hasColumn('properties', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('country');
            }
            if (! Schema::hasColumn('properties', 'property_type')) {
                $table->string('property_type')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('properties', 'status')) {
                $table->string('status')->default('pending')->after('property_type');
            }
        });

        DB::statement('UPDATE properties SET owner_user_id = owner_id WHERE owner_user_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            foreach (['owner_user_id', 'state', 'country', 'pincode', 'property_type', 'status'] as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};