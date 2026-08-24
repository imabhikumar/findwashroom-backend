<?php
// app/Http/Controllers/Api/WalletController.php - COMPLETE FILE

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Payout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    // ============== USER ENDPOINTS ==============
    
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_wallets' => Wallet::count(),
                'total_balance' => Wallet::sum('balance'),
                'active_wallets' => Wallet::where('status', 'active')->count(),
                'average_balance' => round(Wallet::avg('balance') ?? 0, 2),
            ]
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_balance' => Wallet::sum('balance'),
                'total_wallets' => Wallet::count(),
                'active_wallets' => Wallet::where('status', 'active')->count(),
                'pending_payouts' => Payout::where('status', 'pending')->sum('amount') ?? 0,
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = Transaction::with('wallet.user')
            ->paginate($request->per_page ?? 15);
            
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $wallet = Wallet::where('user_id', $request->user_id)->first();
        if (!$wallet) return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
        if ($wallet->balance < $request->amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }

        $payout = Payout::create([
            'user_id' => $request->user_id,
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'request_date' => now()
        ]);

        $wallet->balance -= $request->amount;
        $wallet->save();

        return response()->json(['success' => true, 'data' => $payout, 'message' => 'Payout requested']);
    }

    // ============== ADMIN ENDPOINTS ==============
    
    public function adminList(Request $request)
    {
        $wallets = Wallet::with('user')
            ->when($request->search, function($q, $search) {
                return $q->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);
            
        return response()->json(['success' => true, 'data' => $wallets]);
    }

    public function show($id)
    {
        $wallet = Wallet::with('user')->find($id);
        if (!$wallet) return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
        return response()->json(['success' => true, 'data' => $wallet]);
    }

    public function getUserWallet($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$wallet) return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
        return response()->json(['success' => true, 'data' => $wallet]);
    }

    public function updateBalance(Request $request, $id)
    {
        $request->validate(['balance' => 'required|numeric|min:0']);
        $wallet = Wallet::findOrFail($id);
        $wallet->update(['balance' => $request->balance]);
        return response()->json(['success' => true, 'data' => $wallet, 'message' => 'Balance updated']);
    }

    // ✅ YEH MISSING THA - updateStatus method
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended']);
        $wallet = Wallet::findOrFail($id);
        $wallet->update(['status' => $request->status]);
        return response()->json(['success' => true, 'data' => $wallet, 'message' => 'Status updated']);
    }

    public function addFunds(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $wallet = Wallet::findOrFail($id);
        $wallet->balance += $request->amount;
        $wallet->save();
        return response()->json(['success' => true, 'data' => $wallet, 'message' => 'Funds added']);
    }

    public function deductFunds(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $wallet = Wallet::findOrFail($id);
        if ($wallet->balance < $request->amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }
        $wallet->balance -= $request->amount;
        $wallet->save();
        return response()->json(['success' => true, 'data' => $wallet, 'message' => 'Funds deducted']);
    }

    public function getWalletTransactions($id, Request $request)
    {
        $wallet = Wallet::findOrFail($id);
        $transactions = $wallet->transactions()->paginate($request->per_page ?? 15);
        return response()->json(['success' => true, 'data' => $transactions]);
    }
}