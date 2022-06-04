<?php

namespace App\Repositories\Wildberries;

use App\Models\WbProduct;

class WildberriesBrandRepository
{
    /**
     * @param array $articles
     * @return int
     */
    public function countBrandByProductsArticle(array $articles): int
    {
        return  WbProduct::query()
            ->select('brand')
            ->distinct()
            ->whereIn('nmid', $articles)
            ->count('brand');
    }
}
