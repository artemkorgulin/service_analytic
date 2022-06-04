<?php

return [
    'token' => env('ANALYTICS_TOKEN'),
    'url_v1' => env('ANALYTICS_API_URL'),
    'url_v2' => env('ANALYTICS_API_V2_URL'),
    'enable_logs' => env('ANALYTICS_ENABLE_LOGS', false),
];
