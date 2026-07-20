<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserTrustScore;
use App\Services\TrustService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $admin = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Admin User',
            'mobile' => '9876543210',
            'email' => 'admin@findwashroom.com',
            'role' => 'admin',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $owner = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Owner',
            'mobile' => '9876543211',
            'email' => 'owner@findwashroom.com',
            'role' => 'owner',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $customer = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Customer',
            'mobile' => '9876543212',
            'email' => 'customer@findwashroom.com',
            'role' => 'customer',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        // Generate trust scores
        $trustService = app(TrustService::class);
        foreach ([$admin, $owner, $customer] as $user) {
            // Add verification events
            $trustService->recordVerification($user->id, 'mobile');
            $trustService->recordVerification($user->id, 'email');
            
            // Recalculate and save
            $trustService->recalculateAndSaveScore($user->id);
        }
    }
}