<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    public function test_core_api_routes_are_registered_with_expected_paths(): void
    {
        $routes = app('router')->getRoutes();
        $paths = collect($routes)->map(fn ($route) => $route->uri())->all();

        $this->assertContains('api/v1/admin/login/otp/request', $paths);
        $this->assertContains('api/v1/admin/dashboard', $paths);
        $this->assertContains('api/v1/wallet', $paths);
        $this->assertContains('api/v1/audit-logs', $paths);
    }
}
