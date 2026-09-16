<?php

return [
    'platform' => [
        'app_name' => 'FindWashroom',
        'support_email' => null,
        'support_phone' => null,
        'timezone' => 'UTC',
        'currency' => 'INR',
    ],
    'negotiation' => [
        'sla_minutes' => 30,
        'min_offer_percent' => 80,
        'max_offer_percent' => 120,
        'max_rounds' => 3,
    ],
    'wallet' => [
        'min_topup' => 1,
        'max_topup' => 50000,
        'payout_min' => 100,
        'payout_fee_percent' => 0,
    ],
    'safety' => [
        'sos_ack_seconds' => 60,
        'escalation_levels_enabled' => true,
    ],
    'verification' => [
        'mandatory_levels_per_role' => [
            'customer' => [],
            'owner' => ['identity'],
            'cleaner' => ['identity'],
        ],
    ],
    'feature_flags' => [
        'negotiation_enabled' => true,
        'wallet_enabled' => true,
        'sos_enabled' => true,
        'dynamic_pricing_enabled' => false,
        'group_booking_enabled' => false,
    ],
];