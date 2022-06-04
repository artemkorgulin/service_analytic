<?php

namespace App\Helpers;


class QueryConditionCategoryAnalysis extends QueryCondition
{
    const FIELD_MAP = [
        'brand_id' => 'a.brand_id',
        'subject_id' => 'a.subject_id',
        'subject_name' => 'method_breadcrumbs_category.subject',
        'sku_count' => 'a.sku_count',
        'take' => 'round(a.take / 100, 0)',
        'suppliers_count' => 'a.suppliers_count',
        'avg_take' => 'round((a.take / 100 / a.sku_count), 0)',
    ];
}
