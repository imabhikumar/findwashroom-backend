<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'admins';

    protected $fillable = [
        'email',
        'mobile',
        'role',
        'name',
        'pin',
    ];

    protected $hidden = [
        'pin',
    ];

    protected $casts = [
        'pin' => 'hashed',
    ];
    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_admin_roles', 'admin_id', 'admin_role_id');
    }

}


