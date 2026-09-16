<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payouts MODIFY status VARCHAR(30) NOT NULL DEFAULT 'requested'");
        DB::statement("UPDATE payouts SET status = 'requested' WHERE status = 'pending'");
        DB::statement("UPDATE payouts SET status = 'paid' WHERE status = 'completed'");
    }

    public function down(): void
    {
        DB::statement("UPDATE payouts SET status = 'pending' WHERE status = 'requested'");
        DB::statement("UPDATE payouts SET status = 'completed' WHERE status = 'paid'");
        DB::statement("ALTER TABLE payouts MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending'");
    }
};