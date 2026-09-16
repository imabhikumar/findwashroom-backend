<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(private readonly BookingRepository $repository)
    {
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate($request->query());
    }

    public function findOrFail(int $id)
    {
        return $this->repository->findAdminBookingOrFail($id);
    }

    public function cancel(int $id, string $reason, Request $request)
    {
        return $this->changeStatus($id, 'cancelled', 'cancelled_by_admin', ['reason' => $reason], $request);
    }

    public function forceComplete(int $id, string $reason, Request $request)
    {
        return DB::transaction(function () use ($id, $reason, $request) {
            $booking = $this->repository->findAdminBookingForUpdate($id);
            $old = $booking->toArray();
            $booking = $this->repository->setStatus($booking, 'completed', ['ended_at' => now()]);
            $this->event($booking->id, 'force_completed_by_admin', ['reason' => $reason]);
            $this->audit($request, 'force_complete', $old, $booking->toArray(), $booking->id);
            return $booking->load($this->repository->detailRelations());
        });
    }

    public function extend(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $booking = $this->repository->findAdminBookingForUpdate($id);
            $extension = $this->repository->createExtension($booking->id, $data, $request->user()?->getKey());
            $this->event($booking->id, 'extended_by_admin', $data + ['extension_id' => $extension->id]);
            $this->audit($request, 'extend', $booking->toArray(), $data, $booking->id);
            return $booking->load($this->repository->detailRelations());
        });
    }

    public function stats(): array
    {
        return $this->repository->stats();
    }

    private function changeStatus(int $id, string $status, string $eventType, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $status, $eventType, $data, $request) {
            $booking = $this->repository->findAdminBookingForUpdate($id);
            $old = $booking->toArray();
            $booking = $this->repository->setStatus($booking, $status);
            $this->event($booking->id, $eventType, $data);
            $this->audit($request, $status === 'cancelled' ? 'cancel' : $status, $old, $data + ['status' => $status], $booking->id);
            return $booking->load($this->repository->detailRelations());
        });
    }

    private function event(int $bookingId, string $type, array $data): void
    {
        DB::table('booking_events')->insert([
            'booking_id' => $bookingId,
            'event_type' => $type,
            'event_data' => json_encode($data),
            'created_at' => now(),
        ]);
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $entityId): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->getKey(),
            'user_type' => Admin::class,
            'module' => 'bookings',
            'action' => $action,
            'entity_type' => 'Booking',
            'entity_id' => $entityId,
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}