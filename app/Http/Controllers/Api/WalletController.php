// app/Http/Controllers/Api/WalletController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\AddMoneyRequest;
use App\Http\Requests\Wallet\PayoutRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService)
    {
    }

    public function summary(Request $request)
    {
        $userId = $request->user()->id;
        $walletType = $request->user()->role ?? 'customer';

        $balance = $this->walletService->getBalance($userId, $walletType);
        $wallet = $this->walletService->getWallet($userId, $walletType);

        return $this->successResponse('Wallet summary fetched.', [
            'balance' => $balance,
            'currency' => $wallet?->currency ?? 'INR',
            'status' => $wallet?->status ?? 'active',
            'wallet_type' => $walletType,
        ]);
    }

    public function transactions(Request $request)
    {
        $userId = $request->user()->id;
        $walletType = $request->user()->role ?? 'customer';
        $limit = (int) $request->query('limit', 50);

        $transactions = $this->walletService->getTransactions($userId, $walletType, $limit);

        return $this->successResponse('Transactions fetched.', [
            'transactions' => $transactions,
        ]);
    }

    public function addMoney(AddMoneyRequest $request)
    {
        // This would be called after successful payment
        // For now, just a placeholder
        return $this->errorResponse('This endpoint is for internal use only.', null, 403);
    }

    public function requestPayout(PayoutRequest $request)
    {
        try {
            $payout = $this->walletService->requestPayout(
                $request->user()->id,
                $request->validated('amount'),
                $request->validated('method'),
                $request->validated('account_details', [])
            );

            return $this->successResponse('Payout requested successfully.', [
                'payout' => $payout,
                'balance' => $this->walletService->getBalance($request->user()->id),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
