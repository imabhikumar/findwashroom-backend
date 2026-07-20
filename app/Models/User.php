<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUUID;
use App\Traits\AuditLoggable;
use Illuminate\Database\Eloquent\SoftDeletes;


#[Fillable([
    'name',
    'mobile',
    'email',
    'password',
    'pin',
    'role',
    'rating',
    'total_ratings',
    'is_kyc_verified',
    'status',
])]
#[Hidden(['password', 'pin', 'remember_token'])]
class User extends Authenticatable
{
        use HasApiTokens, HasFactory, Notifiable, HasUUID, AuditLoggable, SoftDeletes;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin' => 'hashed',
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'raised_by');
    }

    public function cleaningJobsAsOwner(): HasMany
    {
        return $this->hasMany(CleaningJob::class, 'owner_id');
    }

    public function cleaningJobsAsCleaner(): HasMany
    {
        return $this->hasMany(CleaningJob::class, 'assigned_cleaner_id');
    }
}
