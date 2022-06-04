<?php

namespace App\Helpers;


class QueryConditionCategorySubcategry extends QueryCondition
{
    const FIELD_MAP = [
        'rubric' => 'b.rubric',
        'rating' => 'round(b.rating, 2)',
        'comments' => 'round(b.comments, 0)',
        'products' => 'b.products',
        'products_with_sale' => 'b.products_with_sale',
        'sales' => 'b.sales',
        'avg_price' => 'round(b.avg_price / 100, 0)',
        'take' => 'round(b.take / 100, 0)',
        'percent_products_with_sale' => '(b.products_with_sale * 100 / b.products)',
        'avg_sale_count_one_product' => '(b.sales / b.products)',
        'avg_sale_count_one_product_with_sale' => '
                                        CASE
                                            WHEN b.products_with_sale = 0 THEN 0
                                            ELSE b.sales / b.products_with_sale
                                        END',
        'count_suppliers' => 'b.count_suppliers',
        'count_suppliers_with_sale' => 'b.count_suppliers_with_sale',
        'percent_suppliers_with_sale' => '(b.count_suppliers_with_sale * 100 / b.count_suppliers)',
        'avg_take_one_product' => 'round((b.take / b.products) / 100, 0)',
        'avg_take_one_product_with_sale' => '
                                        round(CASE
                                            WHEN b.products_with_sale = 0 THEN 0
                                            ELSE b.take / b.products_with_sale / 100
                                        END, 0)',
    ];
}
