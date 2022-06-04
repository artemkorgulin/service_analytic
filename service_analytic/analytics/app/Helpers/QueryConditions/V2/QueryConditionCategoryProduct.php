<?php

namespace App\Helpers\QueryConditions\V2;

use App\Helpers\QueryConditions\QueryConditionForClickhouse;

class QueryConditionCategoryProduct extends QueryConditionForClickhouse
{
    const FIELD_MAP = [
        'fbs',
        'sku',
        'name',
        'color',
        'category',
        'category_count',
        'position',
        'brand',
        'supplier',
        'rating',
        'comments',
        'stock',
        'last_price',
        'min_price',
        'max_price',
        'avg_price',
        'base_price',
        'base_sale',
        'price_with_sale',
        'promo_sale',
        'revenue_potential',
        'lost_profit',
        'lost_profit_percent',
        'days_with_sales',
        'days_in_stock',
        'sales_avg',
        'total_sales',
        'sales_in_stock_avg',
        'revenue'
    ];
}
