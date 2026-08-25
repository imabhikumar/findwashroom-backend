<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CleaningJob;
use App\Models\Complaint;
use App\Models\Property;
use App\Models\Review;
use App\Models\ServiceType;
use App\Models\ServiceUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class P0ApiBusinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([User::class, Property::class, Booking::class, Review::class, Complaint::class, CleaningJob::class, ServiceUnit::class] as $model) {
            $model::unsetEventDispatcher();
        }
    }

    public function test_customer_can_register_and_access_their_account(): void
    {
        $response = $this->postJson('/api/v1/customer/register', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'mobile' => '9876543210',
            'password' => 'secret123',
            'pin' => '1234',
        ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('success', true)
                ->has('data.token')
                ->where('data.user.role', 'customer')
                ->etc());

        $user = User::where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->actingAs($user)->getJson('/api/v1/customer/me')->assertOk();
    }

    public function test_authentication_rejects_invalid_credentials_and_protected_requests_without_authentication(): void
    {
        $user = $this->user(['email' => 'login@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/customer/login/password', [
            'identifier' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertJsonPath('success', false);

        $this->getJson('/api/v1/customer/me')->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = $this->user();
        $this->actingAs($user)->postJson('/api/v1/customer/logout')->assertOk();
        $this->assertCount(0, $user->tokens()->get());
    }

    public function test_public_property_listing_details_and_missing_property(): void
    {
        $owner = $this->user(['role' => 'owner']);
        $property = $this->property($owner);
        $inactive = $this->property($owner, ['is_active' => false]);

        $this->getJson('/api/v1/properties')->assertOk()->assertJsonPath('data.0.id', $property->id);
        $this->getJson('/api/v1/properties/'.$property->id)->assertOk()
            ->assertJsonPath('data.id', $property->id);
        $this->getJson('/api/v1/properties/'.$inactive->id)->assertOk();
        $this->getJson('/api/v1/properties/999999')->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_customer_can_create_and_progress_a_booking_with_database_state(): void
    {
        $owner = $this->user(['role' => 'owner']);
        $customer = $this->user(['role' => 'customer']);
        $property = $this->property($owner, ['price_per_use' => 60]);

        $create = $this->actingAs($customer)->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
        ]);
        $create->assertOk()->assertJsonPath('success', true);
        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertSame($property->id, $booking->property_id);

        $this->actingAs($customer)->postJson('/api/v1/bookings/'.$booking->id.'/start')
            ->assertOk();
        $booking->refresh();
        $this->assertSame('active', $booking->status);
        $this->assertNotNull($booking->start_time);

        $this->actingAs($customer)->postJson('/api/v1/bookings/'.$booking->id.'/end')
            ->assertOk();
        $booking->refresh();
        $this->assertSame('completed', $booking->status);
        $this->assertNotNull($booking->end_time);
        $this->assertGreaterThan(0, (float) $booking->amount);
    }

    public function test_booking_rejects_invalid_data_unauthorized_access_duplicate_and_owner_self_booking(): void
    {
        $owner = $this->user(['role' => 'owner']);
        $customer = $this->user(['role' => 'customer']);
        $property = $this->property($owner);

        $this->actingAs($owner)->postJson('/api/v1/bookings', ['property_id' => $property->id])
            ->assertBadRequest()->assertJsonPath('message', 'Owner cannot book their own property.');
        $this->assertDatabaseMissing('bookings', ['customer_id' => $owner->id, 'property_id' => $property->id]);

        $this->actingAs($customer)->postJson('/api/v1/bookings', [])->assertBadRequest();
        $this->actingAs($customer)->postJson('/api/v1/bookings', ['property_id' => 999999])->assertBadRequest();

        $booking = Booking::create(['property_id' => $property->id, 'customer_id' => $customer->id, 'status' => 'pending']);
        $this->actingAs($customer)->postJson('/api/v1/bookings', ['property_id' => $property->id])
            ->assertBadRequest();
        $this->actingAs($owner)->postJson('/api/v1/bookings/'.$booking->id.'/start')->assertNotFound();
        $this->actingAs($customer)->postJson('/api/v1/bookings/'.$booking->id.'/end')->assertBadRequest();
    }

    public function test_service_unit_listing_available_filter_types_and_missing_detail(): void
    {
        $property = $this->property($this->user(['role' => 'owner']));
        $type = ServiceType::create(['name' => 'Toilet', 'slug' => 'toilet']);
        $available = ServiceUnit::create(['property_id' => $property->id, 'service_type_id' => $type->id, 'name' => 'Unit A', 'capacity' => 1, 'status' => 'available', 'is_active' => true, 'pricing_model' => 'fixed']);
        ServiceUnit::create(['property_id' => $property->id, 'service_type_id' => $type->id, 'name' => 'Unit B', 'capacity' => 1, 'status' => 'closed', 'is_active' => true, 'pricing_model' => 'fixed']);

        $this->getJson('/api/v1/properties/'.$property->id.'/service-units')->assertOk();
        $this->getJson('/api/v1/properties/'.$property->id.'/service-units/available')
            ->assertOk()->assertJsonPath('data.0.id', $available->id);
        $this->getJson('/api/v1/service-units/types')->assertOk();
        $this->getJson('/api/v1/service-units/'.$available->id)->assertOk();
        $this->getJson('/api/v1/service-units/999999')->assertNotFound();
    }

    public function test_completed_customer_booking_can_be_reviewed_once_and_updates_property_aggregate(): void
    {
        [$customer, $property, $booking] = $this->completedBooking();

        $this->actingAs($customer)->postJson('/api/v1/reviews', [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Excellent service',
        ])->assertOk();
        $this->assertDatabaseHas('reviews', ['booking_id' => $booking->id, 'reviewer_id' => $customer->id, 'rating' => 5]);
        $this->assertSame(1, $property->refresh()->total_reviews);
        $this->assertSame('5.00', $property->average_rating);
        $this->actingAs($customer)->postJson('/api/v1/reviews', ['booking_id' => $booking->id, 'rating' => 4])
            ->assertBadRequest();
    }

    public function test_completed_customer_booking_can_create_one_complaint_with_optional_evidence(): void
    {
        [$customer, , $booking] = $this->completedBooking();

        $this->actingAs($customer)->postJson('/api/v1/complaints', ['booking_id' => $booking->id])
            ->assertBadRequest();
        $response = $this->actingAs($customer)->post('/api/v1/complaints', [
            'booking_id' => $booking->id,
            'description' => 'Issue with cleanliness',
            'evidence' => UploadedFile::fake()->image('evidence.jpg'),
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('complaints', ['booking_id' => $booking->id, 'raised_by' => $customer->id, 'status' => 'pending']);
        $this->actingAs($customer)->postJson('/api/v1/complaints', ['booking_id' => $booking->id, 'description' => 'Again'])
            ->assertBadRequest();
    }

    public function test_owner_can_create_cleaning_job_and_cleaner_can_accept_and_submit_proof(): void
    {
        $owner = $this->user(['role' => 'owner']);
        $cleaner = $this->user(['role' => 'cleaner']);
        $property = $this->property($owner);

        $this->actingAs($owner)->postJson('/api/v1/owner/cleaning-jobs', ['property_id' => $property->id, 'price_offer' => 100])->assertOk();
        $job = CleaningJob::firstOrFail();
        $this->assertSame('open', $job->status);
        $this->actingAs($cleaner)->getJson('/api/v1/cleaner/cleaning-jobs')->assertOk();
        $this->actingAs($cleaner)->postJson('/api/v1/cleaner/cleaning-jobs/'.$job->id.'/accept')->assertOk();
        $this->actingAs($cleaner)->post('/api/v1/cleaner/cleaning-jobs/'.$job->id.'/proof', ['proof' => UploadedFile::fake()->image('proof.jpg')])->assertOk();
        $this->assertDatabaseHas('cleaning_jobs', ['id' => $job->id, 'assigned_cleaner_id' => $cleaner->id, 'status' => 'completed']);
    }

    public function test_cleaning_job_owner_scope_and_invalid_proof_are_rejected(): void
    {
        $owner = $this->user(['role' => 'owner']);
        $otherOwner = $this->user(['role' => 'owner']);
        $property = $this->property($owner);
        $this->actingAs($otherOwner)->postJson('/api/v1/owner/cleaning-jobs', ['property_id' => $property->id, 'price_offer' => 100])->assertNotFound();
        $this->assertDatabaseCount('cleaning_jobs', 0);
        $this->actingAs($owner)->postJson('/api/v1/owner/cleaning-jobs', ['property_id' => $property->id])->assertBadRequest();
    }

    public function test_payment_validation_and_unauthorized_access_are_covered_without_calling_gateway(): void
    {
        $this->postJson('/api/v1/payments/order', [])->assertUnauthorized();
        $this->actingAs($this->user(['role' => 'customer']))->postJson('/api/v1/payments/order', [])->assertBadRequest();
    }

    public function test_wallet_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/wallet')->assertUnauthorized();
        $this->getJson('/api/v1/wallet/transactions')->assertUnauthorized();
        $this->postJson('/api/v1/wallet/request-payout', [])->assertUnauthorized();
    }

    private function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'mobile' => fake()->unique()->numerify('##########'),
            'role' => 'customer',
            'status' => 'active',
        ], $attributes));
    }

    private function property(User $owner, array $attributes = []): Property
    {
        return Property::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'Public Washroom',
            'address' => '123 Main Street',
            'city' => 'Mumbai',
            'price_per_use' => 50,
            'is_active' => true,
        ], $attributes));
    }

    private function completedBooking(): array
    {
        $customer = $this->user();
        $property = $this->property($this->user(['role' => 'owner']), ['price_per_use' => 60]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'customer_id' => $customer->id,
            'status' => 'completed',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->subMinutes(1),
            'amount' => 10,
            'payment_status' => 'unpaid',
        ]);

        return [$customer, $property, $booking];
    }
}