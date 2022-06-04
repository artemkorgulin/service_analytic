<?php

return [
    'web_app_api_url' => env('WEB_APP_API_URL'),
    'web_app_api_token' => env('WEB_APP_TOKEN'),
    'analytics_api_url' => env('ANALYTICS_API_URL', 'http://nginx-analytics/api/v1'),
    'analytics_api_token' => env('ANALYTICS_API_TOKEN'),
    'disable_send_data_to_marketplaces' => env('DISABLE_SEND_DATA_TO_MARKETPLACES', false),
];
