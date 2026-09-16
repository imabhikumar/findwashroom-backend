<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('complaint_evidence')) {
            Schema::create('complaint_evidence', function (Blueprint $table) { $table->id(); $table->foreignId('complaint_id')->constrained()->cascadeOnDelete(); $table->string('evidence_type'); $table->text('file_url'); $table->unsignedBigInteger('uploaded_by')->nullable(); $table->timestamp('created_at')->useCurrent(); });
        } else {
            Schema::table('complaint_evidence', function (Blueprint $table) { if (! Schema::hasColumn('complaint_evidence', 'id')) $table->id(); if (! Schema::hasColumn('complaint_evidence', 'complaint_id')) $table->unsignedBigInteger('complaint_id'); if (! Schema::hasColumn('complaint_evidence', 'evidence_type')) $table->string('evidence_type')->nullable(); if (! Schema::hasColumn('complaint_evidence', 'file_url')) $table->text('file_url')->nullable(); if (! Schema::hasColumn('complaint_evidence', 'uploaded_by')) $table->unsignedBigInteger('uploaded_by')->nullable(); if (! Schema::hasColumn('complaint_evidence', 'created_at')) $table->timestamp('created_at')->nullable(); });
        }
        if (! Schema::hasTable('complaint_timeline')) {
            Schema::create('complaint_timeline', function (Blueprint $table) { $table->id(); $table->foreignId('complaint_id')->constrained()->cascadeOnDelete(); $table->unsignedBigInteger('actor_user_id')->nullable(); $table->string('action'); $table->text('note')->nullable(); $table->timestamp('created_at')->useCurrent(); });
        } else {
            Schema::table('complaint_timeline', function (Blueprint $table) { if (! Schema::hasColumn('complaint_timeline', 'id')) $table->id(); if (! Schema::hasColumn('complaint_timeline', 'complaint_id')) $table->unsignedBigInteger('complaint_id'); if (! Schema::hasColumn('complaint_timeline', 'actor_user_id')) $table->unsignedBigInteger('actor_user_id')->nullable(); if (! Schema::hasColumn('complaint_timeline', 'action')) $table->string('action')->nullable(); if (! Schema::hasColumn('complaint_timeline', 'note')) $table->text('note')->nullable(); if (! Schema::hasColumn('complaint_timeline', 'created_at')) $table->timestamp('created_at')->nullable(); });
        }
        if (! Schema::hasTable('disputes')) {
            Schema::create('disputes', function (Blueprint $table) { $table->id(); $table->foreignId('complaint_id')->constrained()->cascadeOnDelete(); $table->string('status')->default('open'); $table->text('resolution')->nullable(); $table->unsignedBigInteger('resolved_by')->nullable(); $table->timestamps(); });
        } else {
            Schema::table('disputes', function (Blueprint $table) { if (! Schema::hasColumn('disputes', 'id')) $table->id(); if (! Schema::hasColumn('disputes', 'complaint_id')) $table->unsignedBigInteger('complaint_id'); if (! Schema::hasColumn('disputes', 'status')) $table->string('status')->default('open'); if (! Schema::hasColumn('disputes', 'resolution')) $table->text('resolution')->nullable(); if (! Schema::hasColumn('disputes', 'resolved_by')) $table->unsignedBigInteger('resolved_by')->nullable(); if (! Schema::hasColumn('disputes', 'created_at')) $table->timestamp('created_at')->nullable(); if (! Schema::hasColumn('disputes', 'updated_at')) $table->timestamp('updated_at')->nullable(); });
        }
        if (! Schema::hasTable('appeals')) {
            Schema::create('appeals', function (Blueprint $table) { $table->id(); $table->foreignId('dispute_id')->constrained()->cascadeOnDelete(); $table->unsignedBigInteger('requested_by')->nullable(); $table->text('reason'); $table->string('status')->default('pending'); $table->timestamp('created_at')->useCurrent(); });
        } else {
            Schema::table('appeals', function (Blueprint $table) { if (! Schema::hasColumn('appeals', 'id')) $table->id(); if (! Schema::hasColumn('appeals', 'dispute_id')) $table->unsignedBigInteger('dispute_id'); if (! Schema::hasColumn('appeals', 'requested_by')) $table->unsignedBigInteger('requested_by')->nullable(); if (! Schema::hasColumn('appeals', 'reason')) $table->text('reason')->nullable(); if (! Schema::hasColumn('appeals', 'status')) $table->string('status')->default('pending'); if (! Schema::hasColumn('appeals', 'created_at')) $table->timestamp('created_at')->nullable(); });
        }
    }
    public function down(): void { Schema::dropIfExists('appeals'); Schema::dropIfExists('disputes'); Schema::dropIfExists('complaint_timeline'); Schema::dropIfExists('complaint_evidence'); }
};