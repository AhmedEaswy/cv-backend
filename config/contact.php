<?php

return [
    'moderation' => [
        'profile_burst_window_minutes' => (int) env('CONTACT_BURST_WINDOW_MINUTES', 15),
        'profile_burst_threshold' => (int) env('CONTACT_BURST_THRESHOLD', 5),
        'max_links_before_review' => (int) env('CONTACT_MAX_LINKS_BEFORE_REVIEW', 3),
    ],
    'turnstile' => [
        'enabled' => (bool) env('TURNSTILE_ENABLED', false),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'site_key' => env('TURNSTILE_SITE_KEY'),
    ],
    'sender_rate_limit' => [
        'max_attempts' => 3,
        'decay_seconds' => 300,
    ],
];
