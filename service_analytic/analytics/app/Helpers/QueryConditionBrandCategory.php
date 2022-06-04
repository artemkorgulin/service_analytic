<?php

namespace App\Helpers;


class QueryConditionBrandCategory extends QueryCondition
{
    const FIELD_MAP = [
        'subject' => 'method_breadcrumbs_category.subject',
        'category' => 'method_breadcrumbs_category.category',
        'rating' => 'round(a.rating, 2)',
        'comments' => 'round(a.comments, 0)',
        'products' => 'a.products',
        'products_with_sale' => 'a.products_with_sale',
        'sales' => 'COALESCE(a.sales, 0)',
        'avg_price' => 'round(a.avg_price, 0)',
        'take' => 'COALESCE(a.take, 0)',
        'percent_products_with_sale' => 'round((a.products_with_sale * 100. / a.products), 0)',
        'avg_sale_count_one_product' => 'COALESCE(round((a.sales / a.products), 0), 0)',
        'avg_sale_count_one_product_with_sale' => '
                                        round(CASE
                                                  WHEN a.products_with_sale = 0 THEN 0
                                                  ELSE a.sales / a.products_with_sale
                                              END, 0)',
        'count_suppliers' => 'a.count_suppliers',
        'count_suppliers_with_sale' => 'a.count_suppliers_with_sale',
        'percent_suppliers_with_sale' => 'CASE
                                            WHEN a.count_suppliers = 0 THEN 0
                                            ELSE round((a.count_suppliers_with_sale * 100. / a.count_suppliers), 0)
                                        END',
        'avg_take_one_product' => 'COALESCE((a.take / a.products), 0)',
        'avg_take_one_product_with_sale' => '
                                        CASE
                                            WHEN a.products_with_sale = 0 THEN 0
                                            ELSE a.take / a.products_with_sale
                                        END',
        'web_id' => 'a.web_id',
        'subject_id' => 'a.subject_id',
    ];
}
