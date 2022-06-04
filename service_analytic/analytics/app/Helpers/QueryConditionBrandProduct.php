<?php

namespace App\Helpers;


class QueryConditionBrandProduct extends QueryCondition
{
    const FIELD_MAP = [
        'sku' => 'method_product_categories_without_dublicate.vendor_code',
        'color' => "COALESCE(card_products.color, '')",

        'take' => 'COALESCE(ceiling(method_aggregate_product_info.revenue/100)::int, 0)',
        'position' => 'method_product_categories_without_dublicate.position',
        'brand' => 'brands.brand',
        'name' => 'card_products.name',
        'subject' => 'method_breadcrumbs_category.subject',
        'category' => 'method_breadcrumbs_category.category',
        'supplier' => 'suppliers."supplier_name"',
        'stock' => 'COALESCE(method_stock.stock_count, 0)',
        'comments' => 'COALESCE(method_product_info.comments_count, card_products.comments, 0)',
        'rating' => 'COALESCE(method_product_info.grade, card_products.grade, 0)',
        'price' => 'COALESCE(method_product_info.final_price/100::int, 0)',
        'days_in_stock' => 'method_aggregate_product_info.day_with_sales',
        'sale_count' => 'method_aggregate_product_info.total_sales',
        'web_id' => 'method_product_categories_without_dublicate.web_id',
        'subject_id' => 'method_product_categories_without_dublicate.subject_id',
    ];
}
