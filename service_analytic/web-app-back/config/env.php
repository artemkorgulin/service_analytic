<?php
return [
    'front_app_url' => env('FRONT_APP_URL', 'localhost'),
    'ozon_host_url' => env('OZON_API_HOST', 'https://cb-api.ozonru.me'),
    'ozon_performance_host_url' => env('OZON_PERFORMANCE_API_HOST', 'https://performance.ozon.ru'),
    'ozon_command_client_id' => env('OZON_COMMAND_CLIENT_ID', ''),
    'ozon_command_api_key' => env('OZON_COMMAND_API_KEY', ''),
    'mpstats_host_url' => env('MPSTATS_API_HOST', 'https://mpstats.io/api/'),
    'mpstats_token' => env('MPSTATS_TOKEN', ''),
    'mail_footer_email' => env('MAIL_FOOTER_EMAIL', ''),
];
