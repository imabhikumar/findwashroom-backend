<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditLoggable
{
    protected static function bootAuditLoggable(): void
    {
        static::created(function ($model) {
            $model->logAudit('create', 'Created', null, $model->toArray());
        });

        static::updated(function ($model) {
            $oldData = $model->getOriginal();
            $newData = $model->getChanges();
            $model->logAudit('update', 'Updated', $oldData, $newData);
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', 'Deleted', $model->toArray(), null);
        });
    }

    public function logAudit(string $action, string $description, ?array $oldData = null, ?array $newData = null): void
    {
        AuditLog::create([
        'user_id' => Auth::id(),
        'user_type' => Auth::user()?->role ?? 'system',
        'action' => $action,
        'module' => $this->getAuditModule(),
        'entity_type' => get_class($this),
        'entity_id' => $this->id,
        'old_data' => $oldData,
        'new_data' => $newData,
        'description' => $description . ' ' . class_basename($this) . ' #' . $this->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'occurred_at' => now(),
    ]);
    }

    protected function getAuditModule(): string
    {
        $mapping = [
            'User' => 'user',
            'Property' => 'property',
            'Booking' => 'booking',
            'Payment' => 'payment',
            'Wallet' => 'wallet',
            'Review' => 'review',
            'Complaint' => 'complaint',
            'CleaningJob' => 'cleaning_job',
            'ServiceUnit' => 'service_unit',
            'Product' => 'product',
        ];

        $className = class_basename($this);
        return $mapping[$className] ?? strtolower($className);
    }
}