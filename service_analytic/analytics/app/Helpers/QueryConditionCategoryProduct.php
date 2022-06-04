<?php

namespace App\Helpers;

class QueryConditionCategoryProduct extends QueryCondition
{
    const FIELD_MAP = [
        'fbs' => '0',
        'sku' => 'cp.vendor_code',
        'name' => 'card_products.name',
        'position' => 'COALESCE(method_last_position_category.position, 0)',
        'color' => "COALESCE(card_products.color, '')",
        'category' => 'COALESCE(method_breadcrumbs_category.category, breadcrumbs_min_web.category)',
        'web_id' => 'COALESCE(method_last_position_category.web_id, cp.web_id)',
        'subject_id' => 'cp.subject_id',
        'brand' => 'b.brand',
        'brand_id' => 'b.brand_id',
        'supplier_name' => 's.supplier_name',
        'supplier_id' => 's.supplier_id',
        'rating' => 'COALESCE(method_product_info.grade, card_products.grade, 0)',
        'comments_count' => 'COALESCE(method_product_info.comments_count, card_products.comments, 0)',
        'stock' => 'COALESCE(method_stock.stock_count, 0)',
        'last_price' => 'COALESCE(method_product_info.final_price/100::int, 0)',
        'min_price' => 'method_aggregate_product_info.min_price/100::int',
        'max_price' => 'method_aggregate_product_info.max_price/100::int',
        'avg_price' => 'ceiling(method_aggregate_product_info.avg_price/100)::int',
        'base_price' => 'COALESCE(method_product_info.price_u/100::int, 0)',
        'base_sale' => 'COALESCE(method_product_info.sale_price, 0)',
        'price_with_sale' => 'COALESCE(method_product_info.sale_price_u/100::int, 0)',
        'promo_sale' => 'COALESCE(method_product_info.promo_sale, 0)',
        'revenue_potential' => 'ceiling(method_aggregate_product_info.revenue_potential/100)::int',
        'lost_profit' => 'ceiling(method_aggregate_product_info.lost_profit/100)::int',
        'lost_profit_percent' => 'round(method_aggregate_product_info.lost_profit_percent, 2)',
        'days_in_stock' => 'method_aggregate_product_info.day_in_stock',
        'days_with_sale' => 'method_aggregate_product_info.day_with_sales',
        'sales_avg' => 'round(method_aggregate_product_info.sales_avg, 1)',
        'total_sales' => 'method_aggregate_product_info.total_sales',
        'sales_in_stock_avg' => 'round(method_aggregate_product_info.sales_in_stock_avg, 1)',
        'revenue' => 'ceiling(method_aggregate_product_info.revenue/100)::int'
    ];
}
