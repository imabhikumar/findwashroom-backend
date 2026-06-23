<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyOtpRoutesTest extends TestCase
{
    public function test_send_otp_legacy_alias_route_exists(): void
    {
        $response = $this->postJson('/api/send-otp', []);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_verify_otp_legacy_alias_route_exists(): void
    {
        $response = $this->postJson('/api/verify-otp', []);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}