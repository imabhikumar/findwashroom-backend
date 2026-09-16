<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Verified', 'slug' => 'verified', 'description' => 'Verified account.', 'type' => 'user'],
            ['name' => 'Trusted', 'slug' => 'trusted', 'description' => 'Trusted platform participant.', 'type' => 'user'],
            ['name' => 'Top Rated', 'slug' => 'top-rated', 'description' => 'Top rated participant or property.', 'type' => 'user'],
            ['name' => 'Women Safe', 'slug' => 'women-safe', 'description' => 'Verified safe environment for women.', 'type' => 'property'],
            ['name' => 'Family Friendly', 'slug' => 'family-friendly', 'description' => 'Suitable for families.', 'type' => 'property'],
        ] as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge + ['criteria' => [], 'is_auto_assign' => false, 'is_active' => true]);
        }
    }
}