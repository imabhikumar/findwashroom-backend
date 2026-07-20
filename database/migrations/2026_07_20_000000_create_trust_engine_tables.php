<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trust Events
        Schema::create('trust_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', '191');
            $table->string('event_category', '191');
            $table->integer('score_change');
            $table->string('reference_type', '191')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            // CHANGE THESE INDEXES - use individual indexes instead of composite
            $table->index(['user_id', 'occurred_at']);
            // Instead of: $table->index(['event_type', 'event_category']);
            $table->index('event_type');      // Individual index
            $table->index('event_category');  // Individual index
            // Instead of: $table->index(['reference_type', 'reference_id']);
            $table->index('reference_type');  // Individual index
            $table->index('reference_id');    // Individual index
        });

        // Badges
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', '191');
            $table->string('slug', '191')->unique();
            $table->string('icon', '191')->nullable();
            $table->text('description')->nullable();
            $table->string('type', '191');
            $table->json('criteria');
            $table->integer('min_trust_score')->nullable();
            $table->boolean('is_auto_assign')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // User Badges
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
            $table->index(['user_id', 'awarded_at']);
        });

        // Property Badges
        Schema::create('property_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'badge_id']);
            $table->index(['property_id', 'awarded_at']);
        });

        // User Trust Scores
        Schema::create('user_trust_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->string('level', '191')->default('unverified');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            // Instead of: $table->index(['score', 'level']);
            $table->index('score');  // Individual index
            $table->index('level');  // Individual index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_trust_scores');
        Schema::dropIfExists('property_badges');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('trust_events');
    }
};