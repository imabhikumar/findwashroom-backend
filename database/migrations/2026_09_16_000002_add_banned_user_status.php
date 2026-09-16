<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY status ENUM('active', 'suspended', 'banned') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET status = 'suspended' WHERE status = 'banned'");
        DB::statement("ALTER TABLE users MODIFY status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
    }
};