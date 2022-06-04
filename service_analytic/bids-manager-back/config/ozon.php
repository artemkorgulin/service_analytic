<?php

return [
    'enable_ozon_service'           => env('ENABLE_OZON_SERVICE', false),
    'perfomance_ozon_api_url'       => 'https://performance.ozon.ru/api',
    'seller_ozon_api_url'           => 'https://api-seller.ozon.ru',
    'seller_ozon_service_client_id' => env('OZON_SELLER_SERVICE_CLIENT_ID'),
    'seller_ozon_service_api_key'   => env('OZON_SELLER_SERVICE_API_KEY'),
    'detail_sku_url'                => 'https://www.ozon.ru/context/detail/id',
    'strategy_sync'                 => env('OZON_SYNC', 1),
    'sync_delay'                    => env('OZON_SYNC_DELAY', 0),
];
