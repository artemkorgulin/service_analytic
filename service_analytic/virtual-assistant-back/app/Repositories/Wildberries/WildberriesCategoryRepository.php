<?php

namespace App\Repositories\Wildberries;

use App\Models\WbProduct;
use \Illuminate\Database\Eloquent\Builder;

class WildberriesCategoryRepository
{
    /**
     * @param string|null $search
     * @return Builder
     */
    public function getAccountProductCategory(string $search = null): Builder
    {
        $categories = WbProduct::query()
            ->distinct('object')
            ->currentUserAndAccount()
            ->with('category', 'directory')
            ->groupBy('parent')
            ->orderBy('object');

        if ($search !== null && strlen($search) > 0) {
            $search = '%' . escapeRawQueryString($search, true) . '%';
            $categories = $categories->where('object', 'LIKE', $search)
                ->orWhere('parent', 'LIKE', $search);
        }

        return $categories;
    }

    /**
     * @param array $articles
     * @return int
     */
    public function countCategoryByProductsArticle(array $articles): int
    {
       return  WbProduct::query()
           ->select('object')
           ->distinct()
           ->whereIn('nmid', $articles)
           ->count('object');
    }
}
