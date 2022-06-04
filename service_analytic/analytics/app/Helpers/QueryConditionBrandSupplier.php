<?php

namespace App\Helpers;


class QueryConditionBrandSupplier extends QueryCondition
{
    const FIELD_MAP = [
        'supplier_id' => 'a.suppliers_id',
        'supplier' => 'suppliers."supplier_name"',
        'rating' => 'round(a.rating, 2)',
        'comments' => 'round(a.comments, 0)',
        'sales' => 'COALESCE(a.sales, 0)',
        'take' => 'COALESCE(a.take, 0)',
        'avg_price' => 'round(a.avg_price, 0)',
        'products' => 'a.products',
        'products_with_sale' => 'a.products_with_sale',
        'percent_products_with_sale' => 'round((a.products_with_sale * 100. / a.products), 0)',
        'avg_sale_count_one_product' => 'COALESCE(round((a.sales / a.products), 0), 0)',
        'avg_sale_count_one_product_with_sale' => '
                                        round(CASE
                                                  WHEN a.products_with_sale = 0 THEN 0
                                                  ELSE a.sales / a.products_with_sale
                                              END, 0)',
        'avg_take_one_product' => 'COALESCE((a.take / a.products), 0)',
        'avg_take_one_product_with_sale' => '
                                        CASE
                                            WHEN a.products_with_sale = 0 THEN 0
                                            ELSE a.take / a.products_with_sale
                                        END',
    ];
}
