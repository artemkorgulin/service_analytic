<?php

return [
    'token' => env('EVENT_MASTER_TOKEN'),
    'api_url' => env('EVENT_MASTER_API_URL', 'localhost'),
    'url_v1' => env('EVENT_MASTER_API_URL'),
    'enable_logs' => env('EVENT_MASTER_ENABLE_LOGS', false),
];
