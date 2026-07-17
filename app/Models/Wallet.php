// app/Models/Wallet.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'wallet_type',
        'balance',
        'currency',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function addBalance(float $amount, string $referenceType = null, int $referenceId = null, string $description = null): WalletTransaction
    {
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'transaction_type' => 'credit',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'status' => 'completed',
        ]);
    }

    public function deductBalance(float $amount, string $referenceType = null, int $referenceId = null, string $description = null): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient balance');
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'transaction_type' => 'debit',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'status' => 'completed',
        ]);
    }
}
