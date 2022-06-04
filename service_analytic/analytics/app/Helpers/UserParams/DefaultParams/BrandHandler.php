<?php

namespace App\Helpers\UserParams\DefaultParams;

use App\Helpers\QueryBuilderHelper;
use App\Models\CardProduct;

class BrandHandler implements HandlerInterface
{
    /**
     * @param  int  $userId
     * @return array
     */
    public function getParams(int $userId): array
    {
        $brandId = '10081';

        $skuWbProducts = QueryBuilderHelper::getUserProducts($userId);
        if ($skuWbProducts->isNotEmpty()) {
            $cardproduct = CardProduct::join('brands', 'brands.brand_id', '=', 'card_products.brand_id')
                ->whereIn('vendor_code', $skuWbProducts)
                ->orderBy('brands.brand')
                ->first();
            if ($cardproduct) {
                $brandId = $cardproduct->brand_id;
            }
        }

        return [
            'brandId' => (string) $brandId,
            'start_date' => date("Y-m-d", strtotime("-31 days")),
            'end_date' => date("Y-m-d", strtotime("-1 days")),
        ];
    }
}
