<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('password');
            $table->timestamp('mobile_verified_at')->nullable()->after('pin');
            $table->timestamp('email_verified_at')->nullable()->after('mobile_verified_at');
        });

        // Make mobile nullable to allow "email only" customers.
        // We avoid ->change() (dbal) by using raw SQL.
        try {
            DB::statement('ALTER TABLE `users` DROP INDEX `users_mobile_unique`');
        } catch (\Throwable) {
            // ignore if index doesn't exist (different db driver / name)
        }

        try {
            DB::statement('ALTER TABLE `users` MODIFY `mobile` VARCHAR(255) NULL');
        } catch (\Throwable) {
            // ignore if DB doesn't support this exact syntax
        }

        try {
            DB::statement('ALTER TABLE `users` ADD UNIQUE `users_mobile_unique` (`mobile`)');
        } catch (\Throwable) {
            // ignore if unique already exists
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin', 'mobile_verified_at', 'email_verified_at']);
        });
    }
};

