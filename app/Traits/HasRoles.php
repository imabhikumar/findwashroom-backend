<?php

namespace App\Traits;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Multi-role support for the User model.
 *
 * `role` on the users table is the *active* role (what RoleMiddleware
 * checks). `userRoles` is the set of roles this identity has been
 * granted and can switch into via POST /auth/switch-role.
 */
trait HasRoles
{
    protected static function bootHasRoles(): void
    {
        static::created(function ($user) {
            if ($user->role) {
                UserRole::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'role' => $user->role,
                ], [
                    'status' => 'active',
                ]);
            }
        });
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->userRoles()->where('role', $role)->exists();
    }

    /**
     * Grant this identity a role (idempotent). Per PDL-008, verification
     * is encouraged but not mandatory, so a newly granted owner/cleaner
     * role is active immediately rather than blocked pending KYC.
     */
    public function grantRole(string $role): UserRole
    {
        return UserRole::query()->firstOrCreate([
            'user_id' => $this->id,
            'role' => $role,
        ], [
            'status' => 'active',
        ]);
    }
}
