<?php

return [
    'type' => [
        'product_optimization' => [
            'method' => 'getOptimizationSegmentsByProducts',
            'range_filter' => true,
        ],
        'category_optimization' => [
            'method' => 'getOptimizationSegmentsByCategory',
            'range_filter' => true,
        ],
        'brand_optimization' => [
            'method' => 'getOptimizationSegmentsByBrand',
            'range_filter' => true,
        ],
        'product_revenue' => [
            'method' => 'getRevenueSegmentsByProducts',
            'analytics_url' => '/wb/dashboard/total-revenue/product',
            'range_filter' => false,
        ],
        'category_revenue' => [
            'method' => 'getRevenueSegmentsByCategory',
            'analytics_url' => '/wb/dashboard/total-revenue/category',
            'range_filter' => false,
        ],
        'brand_revenue' => [
            'method' => 'getRevenueSegmentsByBrand',
            'analytics_url' => '/wb/dashboard/total-revenue/brand',
            'range_filter' => false,
        ],
        'product_ordered' => [
            'method' => 'getOrderedSegmentsByProducts',
            'analytics_url' => '/wb/dashboard/total-ordered/product',
            'range_filter' => false,
        ],
        'category_ordered' => [
            'method' => 'getOrderedSegmentsByCategory',
            'analytics_url' => '/wb/dashboard/total-ordered/category',
            'range_filter' => false,
        ],
        'brand_ordered' => [
            'method' => 'getOrderedSegmentsByBrand',
            'analytics_url' => '/wb/dashboard/total-ordered/brand',
            'range_filter' => false,
        ],
    ],

    'repositories' => [
        [
            'marketplace_id' => 1,
            'title' => 'Ozon',
            'segmentation_class' => \App\Repositories\Ozon\OzonSegmentationRepository::class,
            'repository_class' => \App\Repositories\OzonProductRepository::class,
        ],
        [
            'marketplace_id' => 3,
            'title' => 'Wildberries',
            'segmentation_class' => \App\Repositories\Wildberries\WildberriesSegmentationRepository::class,
            'repository_class' => App\Repositories\Wildberries\WildberriesProductRepository::class,
        ],
    ],

    'segments' => [
        'bad',
        'normal',
        'good',
    ],
];
