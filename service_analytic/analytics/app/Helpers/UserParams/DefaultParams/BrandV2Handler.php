<?php

namespace App\Helpers\UserParams\DefaultParams;

use App\Helpers\QueryBuilderHelper;
use App\Models\CardProduct;

class BrandV2Handler implements HandlerInterface
{
    /**
     * @param  int  $userId
     * @return array
     */
    public function getParams(int $userId): array
    {
        $brand = 'Concept';

        $skuWbProducts = QueryBuilderHelper::getUserProducts($userId);
        if ($skuWbProducts->isNotEmpty()) {
            $cardProduct = CardProduct::join('brands', 'brands.brand_id', '=', 'card_products.brand_id')
                ->whereIn('vendor_code', $skuWbProducts)
                ->orderBy('brands.brand')
                ->first();
            if ($cardProduct) {
                $brand = $cardProduct->brand;
            }
        }

        return [
            'brand' => (string) $brand,
            'start_date' => date('Y-m-d', strtotime('-31 days')),
            'end_date' => date('Y-m-d', strtotime('-1 days')),
        ];
    }
}
