<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    public function test_unauthenticated_api_requests_return_json_401(): void
    {
        $response = $this->getJson('/api/customer/me');

        $response->assertStatus(401)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }
}
