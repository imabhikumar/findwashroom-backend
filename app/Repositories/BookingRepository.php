<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingRepository
{
    public function create(array $payload): Booking
    {
        return Booking::create($payload);
    }

    public function findById(int $bookingId): ?Booking
    {
        return Booking::find($bookingId);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Booking::query()->with(['customer', 'property']);
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['booking_type'])) $query->where('booking_type', $filters['booking_type']);
        if (! empty($filters['property_id'])) $query->where('property_id', $filters['property_id']);
        if (! empty($filters['customer_user_id'])) $query->where('customer_id', $filters['customer_user_id']);
        if (! empty($filters['from_date'])) $query->whereDate('created_at', '>=', $filters['from_date']);
        if (! empty($filters['to_date'])) $query->whereDate('created_at', '<=', $filters['to_date']);
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%"));
            });
        }
        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findAdminBookingOrFail(int $id): Booking
    {
        return Booking::with($this->detailRelations())->findOrFail($id);
    }

    public function findAdminBookingForUpdate(int $id): Booking
    {
        return Booking::with($this->detailRelations())->lockForUpdate()->findOrFail($id);
    }

    public function detailRelations(): array
    {
        return ['customer', 'property', 'serviceUnits', 'products', 'extensions', 'events'];
    }

    public function setStatus(Booking $booking, string $status, array $extra = []): Booking
    {
        $booking->forceFill(array_merge(['status' => $status], $extra))->save();
        return $booking->refresh();
    }

    public function createExtension(int $bookingId, array $data, ?int $adminId)
    {
        return \App\Models\BookingExtension::create([
            'booking_id' => $bookingId,
            'duration_added' => $data['duration_minutes'],
            'amount' => $data['amount'],
            'approved_by' => $adminId,
            'created_at' => now(),
        ]);
    }

    public function stats(): array
    {
        return [
            'today' => Booking::whereDate('created_at', today())->count(),
            'this_week' => Booking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_status' => Booking::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status'),
            'by_type' => Booking::select('booking_type', DB::raw('COUNT(*) as count'))->groupBy('booking_type')->pluck('count', 'booking_type'),
        ];
    }

    public function findByIdAndCustomer(int $bookingId, int $customerId): ?Booking
    {
        return Booking::query()
            ->with('property')
            ->where('id', $bookingId)
            ->where('customer_id', $customerId)
            ->first();
    }

    public function findByIdAndCustomerForUpdate(int $bookingId, int $customerId): ?Booking
    {
        return Booking::query()
            ->with('property')
            ->where('id', $bookingId)
            ->where('customer_id', $customerId)
            ->lockForUpdate()
            ->first();
    }

    public function hasOpenBookingForCustomerProperty(int $customerId, int $propertyId): bool
    {
        return Booking::query()
            ->where('customer_id', $customerId)
            ->where('property_id', $propertyId)
            ->whereIn('status', ['pending', 'active'])
            ->exists();
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Booking::query()
            ->with(['property', 'payment'])
            ->where('customer_id', $customerId)
            ->latest('id')
            ->get();
    }

    public function update(Booking $booking, array $payload): Booking
    {
        $booking->update($payload);
        return $booking->refresh();
    }
}
