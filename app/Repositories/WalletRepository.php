// app/Repositories/WalletRepository.php
<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletRepository
{
    public function getWallet(int $userId, string $walletType = 'customer'): ?Wallet
    {
        return Wallet::where('user_id', $userId)
            ->where('wallet_type', $walletType)
            ->first();
    }

    public function getOrCreateWallet(int $userId, string $walletType = 'customer'): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => $walletType],
            ['balance' => 0, 'currency' => 'INR', 'status' => 'active']
        );
    }

    public function getTransactions(int $walletId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return WalletTransaction::where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getBalance(int $userId, string $walletType = 'customer'): float
    {
        $wallet = $this->getWallet($userId, $walletType);
        return $wallet ? $wallet->balance : 0;
    }

    public function addMoney(int $userId, float $amount, string $referenceType = null, int $referenceId = null, string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $referenceType, $referenceId, $description) {
            $wallet = $this->getOrCreateWallet($userId);
            
            if (!$wallet->isActive()) {
                throw new \Exception('Wallet is not active');
            }

            return $wallet->addBalance($amount, $referenceType, $referenceId, $description);
        });
    }

    public function deductMoney(int $userId, float $amount, string $referenceType = null, int $referenceId = null, string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $referenceType, $referenceId, $description) {
            $wallet = $this->getOrCreateWallet($userId);
            
            if (!$wallet->isActive()) {
                throw new \Exception('Wallet is not active');
            }

            return $wallet->deductBalance($amount, $referenceType, $referenceId, $description);
        });
    }
}
