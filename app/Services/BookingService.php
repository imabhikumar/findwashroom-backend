<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\BookingProduct;
use App\Models\BookingServiceUnit;
use App\Models\Product;
use App\Models\ServiceUnit;
use App\Repositories\BookingRepository;
use App\Repositories\PropertyRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly PropertyRepository $propertyRepository
    ) {
    }

    public function create(int $customerId, array $payload): Booking
    {
        return DB::transaction(function () use ($customerId, $payload) {
            $property = $this->propertyRepository->findById((int) $payload['property_id']);
            if (! $property || ! $property->is_active) {
                throw new NotFoundHttpException('Property not found.');
            }

            if ((int) $property->owner_id === $customerId) {
                throw new BadRequestHttpException('Owner cannot book their own property.');
            }

            if ($this->bookingRepository->hasOpenBookingForCustomerProperty($customerId, (int) $property->id)) {
                throw new BadRequestHttpException('You already have an active or pending booking for this property.');
            }

            $unitIds = array_map('intval', $payload['service_units']);
            $productIds = array_map('intval', $payload['products'] ?? []);

            $units = ServiceUnit::query()
                ->where('property_id', $property->id)
                ->whereIn('id', $unitIds)
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($units->count() !== count($unitIds)) {
                throw new BadRequestHttpException('One or more selected service units are not available for this property.');
            }

            $serviceTotal = 0.0;
            foreach ($unitIds as $unitId) {
                $unit = $units->get($unitId);
                if (! $unit->isAvailable()) {
                    throw new BadRequestHttpException('Selected service unit is unavailable.');
                }

                $activeOccupancy = $unit->bookingServiceUnits()
                    ->whereNull('ended_at')
                    ->whereHas('booking', fn ($query) => $query->whereIn('status', ['pending', 'active']))
                    ->count();

                if ($activeOccupancy >= (int) $unit->capacity) {
                    throw new BadRequestHttpException('Selected service unit is unavailable.');
                }
                $serviceTotal += (float) ($unit->price ?? $property->price_per_use ?? 0);
            }

            $products = collect();
            $productTotal = 0.0;
            if ($productIds) {
                $products = Product::query()
                    ->where('property_id', $property->id)
                    ->whereIn('id', $productIds)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($products->count() !== count($productIds)) {
                    throw new BadRequestHttpException('One or more selected products are not available for this property.');
                }

                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    if (! $product->isInStock()) {
                        throw new BadRequestHttpException('Selected product is out of stock.');
                    }
                    $productTotal += $product->getFinalPrice();
                }
            }

            $total = round($serviceTotal + $productTotal, 2);

            $booking = $this->bookingRepository->create([
                'property_id' => $property->id,
                'customer_id' => $customerId,
                'customer_user_id' => $customerId,
                'booking_number' => 'BK-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                'booking_type' => $payload['booking_type'],
                'scheduled_at' => isset($payload['scheduled_at']) ? Carbon::parse($payload['scheduled_at']) : null,
                'status' => 'pending',
                'amount' => $total,
                'total_amount' => $total,
                'payment_status' => 'unpaid',
            ]);

            foreach ($unitIds as $unitId) {
                $unit = $units->get($unitId);
                BookingServiceUnit::create([
                    'booking_id' => $booking->id,
                    'service_unit_id' => $unit->id,
                    'duration_minutes' => (int) ($unit->default_duration_minutes ?? 10),
                    'price' => (float) ($unit->price ?? $property->price_per_use ?? 0),
                ]);
            }

            foreach ($productIds as $productId) {
                $product = $products->get($productId);
                BookingProduct::create([
                    'booking_id' => $booking->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->getFinalPrice(),
                ]);

                if ($product->stock_quantity !== -1) {
                    $newStock = max(0, (int) $product->stock_quantity - 1);
                    $product->update([
                        'stock_quantity' => $newStock,
                        'availability' => $newStock <= 0 ? 'out_of_stock' : ($newStock <= 5 ? 'limited' : 'available'),
                    ]);
                }
            }

            BookingEvent::create([
                'booking_id' => $booking->id,
                'event_type' => 'created',
                'event_data' => [
                    'customer_id' => $customerId,
                    'service_unit_ids' => $unitIds,
                    'product_ids' => $productIds,
                    'booking_type' => $payload['booking_type'],
                    'total_amount' => $total,
                ],
                'created_at' => now(),
            ]);

            return $booking->load(['property', 'serviceUnits.serviceUnit', 'products.product']);
        });
    }

    public function start(int $customerId, int $bookingId)
    {
        return DB::transaction(function () use ($customerId, $bookingId) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'pending') {
                throw new BadRequestHttpException('Only pending bookings can be started.');
            }

            return $this->bookingRepository->update($booking, [
                'status' => 'active',
                'start_time' => Carbon::now(),
            ]);
        });
    }

    public function end(int $customerId, int $bookingId)
    {
        return DB::transaction(function () use ($customerId, $bookingId) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'active' || ! $booking->start_time) {
                throw new BadRequestHttpException('Booking is not active.');
            }

            $endTime = Carbon::now();
            $minutes = max($booking->start_time->diffInMinutes($endTime), 1);
            $pricePerUse = (float) $booking->property->price_per_use;
            $amount = round(($pricePerUse / 60) * $minutes, 2);

            return $this->bookingRepository->update($booking, [
                'status' => 'completed',
                'end_time' => $endTime,
                'amount' => $amount,
                'payment_status' => 'unpaid',
            ]);
        });
    }

    public function list(int $customerId)
    {
        return $this->bookingRepository->getByCustomer($customerId);
    }
}
