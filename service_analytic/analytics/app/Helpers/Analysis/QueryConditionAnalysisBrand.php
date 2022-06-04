<?php

namespace App\Helpers\Analysis;

use App\Helpers\QueryCondition;

class QueryConditionAnalysisBrand extends QueryCondition
{
    // Brands
    const FIELD_MAP = [
        'suppliers_id' => 'a.suppliers_id',
        'sku_count' => 'a.sku_count',
        'vendor_code' => 'a.vendor_code',
        'last_price' => 'COALESCE(method_product_info.final_price/100::int, 0)',
        'min_price' => 'method_aggregate_product_info.min_price',
        'max_price' => 'method_aggregate_product_info.max_price',
        'avg_price' => 'ceiling(method_aggregate_product_info.avg_price/100)::int',
        'base_price' => 'COALESCE(method_product_info.price_u/100::int, 0)',
        'base_sale' => 'COALESCE(method_product_info.sale_price, 0)',
        'price_with_sale' => 'COALESCE(method_product_info.sale_price_u/100::int, 0)',
        'sales_avg' => 'round(method_aggregate_product_info.sales_avg, 1)',
        'total_sales' => 'method_aggregate_product_info.total_sales',
        'take' => 'ceiling(method_aggregate_product_info.revenue/100)::int',
    ];
}
