<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function summary(): JsonResponse
    {
        $wallet = auth()->user()?->wallet()->first();

        if (!$wallet) {
            return $this->successResponse('Wallet summary fetched.', [
                'wallet_type' => 'customer',
                'balance' => 0,
                'currency' => 'INR',
                'status' => 'active',
            ]);
        }

        return $this->successResponse('Wallet summary fetched.', [
            'id' => $wallet->id,
            'wallet_type' => $wallet->wallet_type,
            'balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'status' => $wallet->status,
        ]);
    }

    public function stats(): JsonResponse
    {
        $user = auth()->user();
        $wallet = $user?->wallet()->first();

        return $this->successResponse('Wallet stats fetched.', [
            'wallet_type' => $wallet?->wallet_type ?? 'customer',
            'balance' => (float) ($wallet?->balance ?? 0),
            'pending_payouts' => (float) Payout::where('user_id', $user?->id)->where('status', 'pending')->sum('amount'),
        ]);
    }

    public function transactions(Request $request)
    {
        $wallet = auth()->user()?->wallet()->first();

        $transactions = $wallet
            ? $wallet->transactions()->orderByDesc('created_at')->paginate($request->per_page ?? 15)
            : collect();

        return $this->successResponse('Wallet transactions fetched.', $transactions);
    }

    public function addMoney(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $wallet = auth()->user()?->wallet()->firstOrCreate([
            'wallet_type' => 'customer',
        ], [
            'balance' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ]);

        $wallet->balance += $request->amount;
        $wallet->save();

        $wallet->transactions()->create([
            'transaction_type' => 'credit',
            'amount' => $request->amount,
            'balance_after' => $wallet->balance,
            'description' => $request->description ?? 'Added to wallet',
            'status' => 'completed',
        ]);

        return $this->successResponse('Money added successfully.', [
            'wallet_id' => $wallet->id,
            'balance' => (float) $wallet->balance,
        ]);
    }

    public function requestPayout(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payout_method' => 'required|in:bank_transfer,upi,razorpay',
            'account_details' => 'required|array',
            'account_details.account_number' => 'required_if:payout_method,bank_transfer|string|max:255',
            'account_details.ifsc' => 'required_if:payout_method,bank_transfer|string|max:255',
            'account_details.upi_id' => 'required_if:payout_method,upi|string|max:255',
        ]);

        $wallet = auth()->user()?->wallet()->first();

        if (!$wallet) {
            return $this->errorResponse('Wallet not found.', null, 404);
        }

        if ($wallet->balance < $request->amount) {
            return $this->errorResponse('Insufficient balance.', null, 400);
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        $payout = Payout::create([
            'wallet_id' => $wallet->id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'payout_method' => $request->payout_method,
            'account_details' => $request->account_details,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $wallet->transactions()->create([
            'transaction_type' => 'debit',
            'amount' => $request->amount,
            'balance_after' => $wallet->balance,
            'description' => 'Payout requested',
            'reference_type' => 'payout',
            'reference_id' => $payout->id,
            'status' => 'completed',
        ]);

        return $this->successResponse('Payout requested.', [
            'id' => $payout->id,
            'amount' => (float) $payout->amount,
            'payout_method' => $payout->payout_method,
            'status' => $payout->status,
        ]);
    }

    public function adminList(Request $request)
    {
        $wallets = Wallet::with('user')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);

        return $this->successResponse('Wallets fetched.', $wallets);
    }

    public function show($id)
    {
        $wallet = Wallet::with('user')->find($id);
        if (!$wallet) {
            return $this->errorResponse('Wallet not found.', null, 404);
        }

        return $this->successResponse('Wallet fetched.', $wallet);
    }

    public function getUserWallet($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$wallet) {
            return $this->errorResponse('Wallet not found.', null, 404);
        }

        return $this->successResponse('User wallet fetched.', $wallet);
    }

    public function updateBalance(Request $request, $id)
    {
        $request->validate(['balance' => 'required|numeric|min:0']);
        $wallet = Wallet::findOrFail($id);
        $wallet->update(['balance' => $request->balance]);

        return $this->successResponse('Balance updated.', $wallet);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended']);
        $wallet = Wallet::findOrFail($id);
        $wallet->update(['status' => $request->status]);

        return $this->successResponse('Wallet status updated.', $wallet);
    }

    public function adjustBalance(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        $wallet = Wallet::findOrFail($id);
        $wallet->balance += $request->amount;
        $wallet->save();

        $wallet->transactions()->create([
            'transaction_type' => $request->amount >= 0 ? 'credit' : 'debit',
            'amount' => abs($request->amount),
            'balance_after' => $wallet->balance,
            'description' => $request->reason ?? 'Admin adjustment',
            'reference_type' => 'adjustment',
            'status' => 'completed',
        ]);

        return $this->successResponse('Wallet adjusted.', $wallet);
    }

    public function addFunds(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $wallet = Wallet::findOrFail($id);
        $wallet->balance += $request->amount;
        $wallet->save();

        return $this->successResponse('Funds added.', $wallet);
    }

    public function deductFunds(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $wallet = Wallet::findOrFail($id);
        if ($wallet->balance < $request->amount) {
            return $this->errorResponse('Insufficient balance.', null, 400);
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        return $this->successResponse('Funds deducted.', $wallet);
    }

    public function getWalletTransactions($id, Request $request)
    {
        $wallet = Wallet::findOrFail($id);
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate($request->per_page ?? 15);

        return $this->successResponse('Wallet transactions fetched.', $transactions);
    }
}