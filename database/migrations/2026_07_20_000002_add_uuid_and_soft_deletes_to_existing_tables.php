<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add UUIDs
        $tables = [
            'users',
            'properties',
            'bookings',
            'payments',
            'reviews',
            'complaints',
            'cleaning_jobs',
            'service_units',
            'products',
            'wallets',
            'wallet_transactions',
            'deposits',
            'payouts',
            'badges',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $schema) use ($table) {
                    $schema->uuid('uuid')->unique()->nullable()->after('id');
                });
            }
        }

        // Add Soft Deletes
        $softDeleteTables = [
            'users',
            'properties',
            'bookings',
            'payments',
            'reviews',
            'complaints',
            'cleaning_jobs',
            'service_units',
            'products',
            'wallets',
            'badges',
        ];

        foreach ($softDeleteTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $schema) use ($table) {
                    $schema->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        // Remove soft deletes and UUIDs (not recommended to reverse)
        // These changes are destructive, so we'll just skip
    }
};