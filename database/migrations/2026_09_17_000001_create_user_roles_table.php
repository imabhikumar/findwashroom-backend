<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Introduces multi-role support (PDL-002 / SRS Module 1 "Switch Role"):
     * a single user identity can hold several of [customer, owner, cleaner].
     * `users.role` remains the user's *currently active* role (what
     * RoleMiddleware checks on every request); `user_roles` is the set of
     * roles they are allowed to switch into.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['customer', 'owner', 'cleaner']);
            $table->enum('status', ['active', 'pending_verification'])->default('active');
            $table->timestamps();
            $table->unique(['user_id', 'role']);
        });

        // Backfill: every existing user already holds the role currently
        // stored on their row, so they aren't locked out after this ships.
        DB::table('users')
            ->select('id', 'role')
            ->whereIn('role', ['customer', 'owner', 'cleaner'])
            ->orderBy('id')
            ->chunkById(500, function ($users) {
                $now = now();
                $rows = $users->map(fn ($user) => [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if (! empty($rows)) {
                    DB::table('user_roles')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
