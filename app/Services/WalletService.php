// app/Services/WalletService.php
<?php

namespace App\Services;

use App\Repositories\WalletRepository;
use App\Models\Wallet;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(private readonly WalletRepository $walletRepository)
    {
    }

    public function getWallet(int $userId, string $walletType = 'customer'): ?Wallet
    {
        return $this->walletRepository->getOrCreateWallet($userId, $walletType);
    }

    public function getBalance(int $userId, string $walletType = 'customer'): float
    {
        return $this->walletRepository->getBalance($userId, $walletType);
    }

    public function getTransactions(int $userId, string $walletType = 'customer', int $limit = 50)
    {
        $wallet = $this->walletRepository->getOrCreateWallet($userId, $walletType);
        return $this->walletRepository->getTransactions($wallet->id, $limit);
    }

    public function addMoney(int $userId, float $amount, string $referenceType = null, int $referenceId = null, string $description = null)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        return $this->walletRepository->addMoney($userId, $amount, $referenceType, $referenceId, $description);
    }

    public function deductMoney(int $userId, float $amount, string $referenceType = null, int $referenceId = null, string $description = null)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        if ($this->getBalance($userId) < $amount) {
            throw new \Exception('Insufficient balance');
        }

        return $this->walletRepository->deductMoney($userId, $amount, $referenceType, $referenceId, $description);
    }

    public function requestPayout(int $userId, float $amount, string $method, array $accountDetails = []): Payout
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        $balance = $this->getBalance($userId);
        if ($balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        return DB::transaction(function () use ($userId, $amount, $method, $accountDetails) {
            // Deduct from wallet
            $this->walletRepository->deductMoney(
                $userId,
                $amount,
                'payout',
                null,
                "Payout request via {$method}"
            );

            // Create payout record
            $wallet = $this->walletRepository->getOrCreateWallet($userId);
            
            return Payout::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'amount' => $amount,
                'payout_method' => $method,
                'account_details' => json_encode($accountDetails),
                'status' => 'pending',
                'requested_at' => now(),
            ]);
        });
    }
}
