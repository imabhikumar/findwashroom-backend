<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\ServiceType;
use App\Models\ServiceUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPropertyDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_list_supports_search_category_availability_price_and_radius(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $type = ServiceType::query()->create([
            'name' => 'Toilet',
            'slug' => 'toilet-' . uniqid(),
            'default_duration_minutes' => 10,
            'is_active' => true,
        ]);

        $nearby = Property::query()->create([
            'owner_id' => $owner->id,
            'owner_user_id' => $owner->id,
            'name' => 'Nearby Toilet',
            'address' => 'Near Road',
            'city' => 'Noida',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'property_type' => 'toilet',
            'status' => 'approved',
            'price_per_use' => 20,
            'is_active' => true,
        ]);

        Property::query()->create([
            'owner_id' => $owner->id,
            'owner_user_id' => $owner->id,
            'name' => 'Far Toilet',
            'address' => 'Far Road',
            'city' => 'Delhi',
            'latitude' => 28.7041,
            'longitude' => 77.1025,
            'property_type' => 'toilet',
            'status' => 'approved',
            'price_per_use' => 80,
            'is_active' => true,
        ]);

        ServiceUnit::query()->create([
            'property_id' => $nearby->id,
            'service_type_id' => $type->id,
            'name' => 'Female Toilet',
            'capacity' => 2,
            'default_duration_minutes' => 10,
            'price' => 20,
            'pricing_model' => 'fixed',
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/properties?search=Nearby&category=toilet&availability=available&price_max=30&lat=28.6139&lng=77.2090&radius=1&sort=distance');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $nearby->id);

        $this->assertEquals(0.0, (float) $response->json('data.0.distance_km'));
    }
}
