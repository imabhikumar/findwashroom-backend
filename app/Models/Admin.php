<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
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
}

