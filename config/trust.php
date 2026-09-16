<?php

return [
    'event_types' => [
        'verification_phone', 'verification_email', 'verification_identity',
        'booking_completed', 'review_received', 'complaint_resolved',
        'complaint_raised', 'policy_violation', 'payment_issue', 'admin_adjustment',
    ],
    'score_min' => -100,
    'score_max' => 100,
];