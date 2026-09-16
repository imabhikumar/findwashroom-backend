<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private readonly PaymentRepository $repository)
    {
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginatePayments($request->query());
    }

    public function findOrFail(int $id)
    {
        return $this->repository->findPaymentOrFail($id);
    }

    public function refund(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $payment = $this->repository->findPaymentForUpdate($id);
            $original = (float) $payment->amount;
            $amount = (float) $data['amount'];

            if ($amount > $original) {
                abort(422, 'Refund amount cannot exceed payment amount.');
            }

            $wallet = $this->repository->customerWallet($payment->booking->customer_id);
            $wallet->forceFill([
                'balance' => (float) $wallet->balance + $amount,
            ])->save();

            $transaction = $wallet->transactions()->create([
                'transaction_type' => 'refund',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'description' => $data['reason'],
                'status' => 'completed',
            ]);

            $status = $amount < $original ? 'partially_refunded' : 'refunded';
            $old = $payment->toArray();
            $payment = $this->repository->setStatus($payment, $status);
            $this->audit($request, 'refund', $old, [
                'status' => $status,
                'amount' => $amount,
                'reason' => $data['reason'],
                'wallet_transaction_id' => $transaction->id,
            ], $payment->id);

            return $payment->load(['booking.customer', 'booking.property']);
        });
    }

    public function stats(): array
    {
        return $this->repository->paymentStats();
    }

    public function payouts(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginatePayouts($request->query());
    }

    public function approvePayout(int $id, Request $request)
    {
        return $this->payoutStatus($id, 'paid', null, $request);
    }

    public function rejectPayout(int $id, string $reason, Request $request)
    {
        return $this->payoutStatus($id, 'rejected', $reason, $request);
    }

    private function payoutStatus(int $id, string $status, ?string $reason, Request $request)
    {
        return DB::transaction(function () use ($id, $status, $reason, $request) {
            $payout = $this->repository->findPayoutForUpdate($id);
            $old = $payout->toArray();
            $payout = $this->repository->setPayoutStatus($payout, $status, $reason);
            $this->audit($request, $status === 'paid' ? 'approve' : 'reject', $old, [
                'status' => $status,
                ...($reason ? ['reason' => $reason] : []),
            ], $payout->id, 'Payout');
            return $payout->load('user');
        });
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $entityId, string $entityType = 'Payment'): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->getKey(),
            'user_type' => Admin::class,
            'module' => strtolower($entityType) . 's',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}