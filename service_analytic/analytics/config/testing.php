<?php

return [
    'base_api_url' => env('WEB_APP_API_URL'),
    'users' => [
        [
            'email' => env('TEST_USER_EMAIL', 'test@example.com'),
            'password' => env('TEST_USER_PASSWORD', 'Test123'),
        ]
    ]
];
