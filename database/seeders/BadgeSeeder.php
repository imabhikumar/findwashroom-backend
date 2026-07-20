<?php

namespace Database\Seeders;
// database/seeders/BadgeSeeder.php

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        // User Badges
        $badges = [
            [
                'name' => 'Verified Phone',
                'slug' => 'verified-phone',
                'icon' => '📱',
                'description' => 'Phone number verified',
                'type' => 'user',
                'criteria' => ['event_type' => 'verification_phone', 'min_count' => 1],
                'min_trust_score' => 10,
                'is_auto_assign' => true,
            ],
            [
                'name' => 'Trusted Customer',
                'slug' => 'trusted-customer',
                'icon' => '⭐',
                'description' => 'Completed 10+ bookings',
                'type' => 'user',
                'criteria' => ['event_type' => 'booking_completed', 'min_count' => 10],
                'min_trust_score' => 40,
                'is_auto_assign' => true,
            ],
            [
                'name' => 'Premium User',
                'slug' => 'premium-user',
                'icon' => '👑',
                'description' => 'Trust score above 80',
                'type' => 'user',
                'criteria' => [],
                'min_trust_score' => 80,
                'is_auto_assign' => true,
            ],
            [
                'name' => 'Verified Owner',
                'slug' => 'verified-owner',
                'icon' => '✅',
                'description' => 'Property verified and trusted',
                'type' => 'user',
                'criteria' => ['event_type' => 'verification_property', 'min_count' => 1],
                'min_trust_score' => 30,
                'is_auto_assign' => true,
            ],
            
            // Property Badges
            [
                'name' => 'Women Safe',
                'slug' => 'women-safe',
                'icon' => '👩',
                'description' => 'Safe for women (verified by community)',
                'type' => 'property',
                'criteria' => ['min_rating' => 4.5, 'min_reviews' => 10],
                'min_trust_score' => null,
                'is_auto_assign' => true,
            ],
            [
                'name' => 'Family Safe',
                'slug' => 'family-safe',
                'icon' => '👨‍👩‍👧‍👦',
                'description' => 'Safe for families (verified by community)',
                'type' => 'property',
                'criteria' => ['min_rating' => 4.0, 'min_reviews' => 5],
                'min_trust_score' => null,
                'is_auto_assign' => true,
            ],
            [
                'name' => 'Premium Hygiene',
                'slug' => 'premium-hygiene',
                'icon' => '✨',
                'description' => 'Top rated hygiene standards',
                'type' => 'property',
                'criteria' => ['min_rating' => 4.8, 'min_reviews' => 20],
                'min_trust_score' => null,
                'is_auto_assign' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}