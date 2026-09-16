<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'gateway')) $table->string('gateway')->nullable();
            if (! Schema::hasColumn('payments', 'gateway_transaction_id')) $table->string('gateway_transaction_id')->nullable();
            if (! Schema::hasColumn('payments', 'deleted_at')) $table->softDeletes();
        });

        DB::statement("ALTER TABLE payments MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending'");
        DB::statement('UPDATE payments SET gateway = payment_gateway WHERE gateway IS NULL');
        DB::statement('UPDATE payments SET gateway_transaction_id = transaction_id WHERE gateway_transaction_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['gateway', 'gateway_transaction_id', 'deleted_at'] as $column) {
                if (Schema::hasColumn('payments', $column)) $table->dropColumn($column);
            }
        });
    }
};