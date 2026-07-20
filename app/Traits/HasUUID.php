<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUUID
{
    protected static function bootHasUUID(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}