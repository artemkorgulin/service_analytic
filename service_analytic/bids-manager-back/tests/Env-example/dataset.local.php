<?php

return [
    'user_id' => 2,
    'token' => 'user_test_token',
    'platforms' => [
        'ozon' => [
            'id' => '1',
            'title' => 'Ozon',
            'description' => 'API для работы с товарами и продажами',
            'api_url' => 'https://api-seller.ozon.ru',
        ],
        'performance' => [
            'id' => '2',
            'title' => 'Ozon Performance',
            'description' => 'API для управления рекламой',
            'api_url' => 'https://performance.ozon.ru/api',
        ]
    ],
    'accounts' => [
        'ozon' => [
            'id' => 1,
            'platform_client_id' => 'ozon_client_id',
            'platform_api_key' => 'ozon_api_key',
            'platform_id' => 1
        ],
        'performance' => [
            'id' => 2,
            'platform_client_id' => 'performance_client_id',
            'platform_api_key' => 'performance_api_key',
            'platform_id' => 2
        ],
        'wildberries' => [
            'id' => 3,
            'platform_client_id' => 'wildberries_client_id',
            'platform_api_key' => 'wildberries_api_key',
            'platform_id' => 3
        ]
    ],
    'campaigns' => [
        'campaign_good_id' => [
            24253, 15396
        ],
        'ozon_statistic_campaigns' => [
            'ids' => [
                "250059"
            ],
            'objects' => []
        ],
        'ids' => ['270486', '41769', '3752'],
        'not_access_ids' => [2400],
        'keywords' => [
            'campaign_id' => 270486,
            'campaign_good_id' => 24253,
            'group_id' => 446
        ],
        'stop_words' => [
            'campaign_id' => 270486,
            'campaign_good_id' => 24253,
            'group_id' => 446,
            'names' => [
                0 => 'лореаль',
                1 => 'натура',
                2 => 'одноразовая'
            ],
            'ids' => [44, 45, 46]
        ]
    ]
];
