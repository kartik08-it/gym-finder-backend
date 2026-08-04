<?php

return [
    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'nominatim' => [
        'url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
        'user_agent' => env('NOMINATIM_USER_AGENT', 'GymFinder/1.0'),
    ],
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
    ],
    'otp' => [
        'provider' => env('OTP_PROVIDER', 'dummy'),
        'ttl_seconds' => env('OTP_TTL_SECONDS', 300),
    ],
];
