<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->json('setting_value');
            $table->string('setting_group');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index('setting_group');
        });

        foreach ([
            'commission_rules' => ['commission_type', 'value'],
            'refund_rules' => ['refund_type', 'value'],
            'cancellation_rules' => ['penalty_type', 'value'],
        ] as $tableName => [$typeColumn, $valueColumn]) {
            Schema::create($tableName, function (Blueprint $table) use ($typeColumn, $valueColumn) {
                $table->id();
                $table->string('rule_name');
                $table->json('conditions')->nullable();
                $table->string($typeColumn);
                $table->decimal($valueColumn, 12, 2)->default(0);
                $table->unsignedInteger('priority')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['is_active', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_rules');
        Schema::dropIfExists('refund_rules');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('dynamic_settings');
    }
};