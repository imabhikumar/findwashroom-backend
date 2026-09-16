<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Models\Payout;
use App\Models\Wallet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    public function paginatePayments(array $filters): LengthAwarePaginator
    {
        $query = Payment::query()->with(['booking.customer', 'booking.property']);

        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['gateway'])) $query->where(function ($q) use ($filters) {
            $q->where('gateway', $filters['gateway'])->orWhere('payment_gateway', $filters['gateway']);
        });
        if (! empty($filters['booking_id'])) $query->where('booking_id', $filters['booking_id']);
        if (! empty($filters['from_date'])) $query->whereDate('created_at', '>=', $filters['from_date']);
        if (! empty($filters['to_date'])) $query->whereDate('created_at', '<=', $filters['to_date']);
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('booking.customer', fn ($customer) => $customer
                        ->where('email', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findPaymentOrFail(int $id): Payment
    {
        return Payment::with(['booking.customer', 'booking.property'])->findOrFail($id);
    }

    public function findPaymentForUpdate(int $id): Payment
    {
        return Payment::with(['booking.customer', 'booking.property'])->lockForUpdate()->findOrFail($id);
    }

    public function setStatus(Payment $payment, string $status): Payment
    {
        $payment->forceFill(['status' => $status])->save();
        return $payment->refresh();
    }

    public function customerWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => 'customer'],
            ['balance' => 0, 'currency' => 'INR', 'status' => 'active']
        );
    }

    public function paymentStats(): array
    {
        return [
            'today_total' => Payment::where('status', 'success')->whereDate('created_at', today())->sum('amount'),
            'this_week_total' => Payment::where('status', 'success')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount'),
            'this_month_total' => Payment::where('status', 'success')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'by_status' => Payment::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status'),
            'refunds_issued' => DB::table('wallet_transactions')->where('transaction_type', 'refund')->sum('amount'),
        ];
    }

    public function paginatePayouts(array $filters): LengthAwarePaginator
    {
        return Payout::with('user')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findPayoutForUpdate(int $id): Payout
    {
        return Payout::lockForUpdate()->findOrFail($id);
    }

    public function setPayoutStatus(Payout $payout, string $status, ?string $reason): Payout
    {
        $payout->forceFill([
            'status' => $status,
            'processed_at' => now(),
            'failure_reason' => $reason,
        ])->save();

        return $payout->refresh();
    }

    public function create(array $payload): Payment
    {
        return Payment::create($payload);
    }

    public function findByBookingId(int $bookingId): ?Payment
    {
        return Payment::query()->where('booking_id', $bookingId)->latest('id')->first();
    }

    public function findPendingByBookingId(int $bookingId): ?Payment
    {
        return Payment::query()
            ->where('booking_id', $bookingId)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    public function findByGatewayOrderId(string $gatewayOrderId): ?Payment
    {
        return Payment::query()
            ->where('gateway_order_id', $gatewayOrderId)
            ->first();
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::query()
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function update(Payment $payment, array $payload): Payment
    {
        $payment->update($payload);
        return $payment->refresh();
    }
}
