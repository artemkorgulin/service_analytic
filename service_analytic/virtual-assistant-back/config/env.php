<?php
return [
    'front_app_url' => getenv('FRONT_APP_URL', 'localhost'),
    'ozon_host_url' => getenv('OZON_API_HOST', 'https://cb-api.ozonru.me'),
    'ozon_api_seller_v1_url' => getenv('OZON_API_SELLER_V1_URL', 'https://api-seller.ozon.ru/v1/'),
    'ozon_command_client_id' => getenv('OZON_COMMAND_CLIEND_ID', ''),
    'ozon_command_api_key' => getenv('OZON_COMMAND_API_KEY', ''),
    'mpstats_host_url' => getenv('MPSTATS_API_HOST', 'https://mpstats.io/api/'),
    'mpstats_token' => getenv('MPSTATS_TOKEN', ''),
    'ozon_chunk_tokens' => getenv('OZON_CHUNK_TOKENS'),
    'check_advertising_url' => getenv('CHECK_ADVERTISING_URL'),
    'check_advertising_token' => getenv('CHECK_ADVERTISING_TOKEN'),
    'wildberries_api_token' => getenv('WILDBERRIES_API_TOKEN'),
    'wildberries_client_id' => getenv('WILDBERRIES_CLIENT_ID'),
    'ireg_api_url' => getenv('IREG_API_URL', 'https://partner.ireg.pro/api/v1'),
    'ireg_api_key' => getenv('IREG_API_KEY', ''),
    'ireg_storage' => getenv('IREG_STORAGE', 'ireg'),
    'sentry_enabled' => env('SENTRY_ENABLED', false),
];
