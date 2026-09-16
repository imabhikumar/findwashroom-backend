<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $columns = ['complaint_number', 'against_user_id', 'against_property_id', 'category', 'priority', 'sla_due_at', 'resolved_at'];
            if (! Schema::hasColumn('complaints', 'complaint_number')) $table->string('complaint_number')->nullable()->after('id');
            if (! Schema::hasColumn('complaints', 'against_user_id')) $table->unsignedBigInteger('against_user_id')->nullable();
            if (! Schema::hasColumn('complaints', 'against_property_id')) $table->unsignedBigInteger('against_property_id')->nullable();
            if (! Schema::hasColumn('complaints', 'category')) $table->string('category')->nullable();
            if (! Schema::hasColumn('complaints', 'priority')) $table->string('priority')->default('medium');
            if (! Schema::hasColumn('complaints', 'sla_due_at')) $table->timestamp('sla_due_at')->nullable();
            if (! Schema::hasColumn('complaints', 'resolved_at')) $table->timestamp('resolved_at')->nullable();
        });
        DB::statement("ALTER TABLE complaints MODIFY status VARCHAR(40) NOT NULL DEFAULT 'raised'");
        DB::statement("UPDATE complaints SET complaint_number = CONCAT('CMP-', id) WHERE complaint_number IS NULL");
        DB::statement("UPDATE complaints SET status = 'raised' WHERE status = 'pending'");
    }
    public function down(): void {}
};