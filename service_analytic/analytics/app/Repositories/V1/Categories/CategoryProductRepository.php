<?php

namespace App\Repositories\V1\Categories;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\StatisticQueries;
use App\Contracts\Repositories\V1\Categories\CategoryProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelCte\Query\Builder as BuilderCte;

class CategoryProductRepository implements CategoryProductRepositoryInterface
{
    protected int $categoryId;
    protected int|null $subjectId;
    protected mixed $startDate;
    protected mixed $endDate;
    private Collection|null $skuWbProducts;

    /**
     * @param  array  $requestParams
     */
    public function __construct(array $requestParams)
    {
        $skuWbProducts = null;
        if (isset($requestParams['myProducts']) && $requestParams['myProducts']) {
            $userId = $requestParams['user']['id'];
            $skuWbProducts = QueryBuilderHelper::getUserProducts($userId);
        }

        $this->categoryId = $requestParams['categoryId'];
        $this->subjectId = $requestParams['subjectId'];
        $this->startDate = $requestParams['startDate'];
        $this->endDate = $requestParams['endDate'];
        $this->skuWbProducts = $skuWbProducts;
    }

    public function getProductsByCategoryFilters(string $selectBlock): Builder|BuilderCte
    {
        $categoryId = $this->categoryId;
        $subjectId = $this->subjectId;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $products = DB::table('cte_products as cp')
            // получаем категории в отчёте
            ->withExpression('cte_web_id', function ($query) use ($categoryId) {
                $query->withExpression('cte_cat_tree', function ($categoryQuery) use ($categoryId) {
                    $categoryQuery->select('ct.lft', 'ct.rgt')
                        ->from('category_trees as ct')
                        ->where('ct.web_id', $categoryId)
                        ->whereNull('ct.deleted_at');
                })
                    ->select('ct2.web_id')
                    ->from('category_trees as ct2')
                    ->whereRaw('ct2.lft >= (select cte_cat_tree.lft from cte_cat_tree)')
                    ->whereRaw('ct2.rgt <= (select cte_cat_tree.rgt from cte_cat_tree)');
            })
            // получаем продукты по категории с последней предметной категорией
            ->withExpression('cte_products', function ($query) use ($endDate, $subjectId) {
                $query->withExpression('cte_last_subject', function ($subjectQuery) use ($endDate, $subjectId) {
                    $subjectQuery->selectRaw(
                        'cv.vendor_code, cv.subject_id, cv.web_id,
                                rank() over (partition by cv.vendor_code order by cv.date desc)
                            ')
                        ->from('category_vendor as cv')
                        ->whereRaw('cv.web_id in (table cte_web_id)');
                    if (isset($subjectId)) {
                        $subjectQuery->where('cv.subject_id', $subjectId)
                            ->where('cv.date', '<=', $endDate);
                    } else {
                        $subjectQuery->where('cv.date', '<=', $endDate);
                    }
                })
                    ->selectRaw('ls.vendor_code, ls.subject_id, min(ls.web_id) as web_id')
                    ->from('cte_last_subject as ls')
                    ->where('rank', 1)
                    ->groupBy('ls.vendor_code', 'ls.subject_id');

            })
            ->selectRaw($selectBlock)
            ->join(DB::raw(StatisticQueries::getAggregateProductInfo('cp', $startDate, $endDate)),
                'method_aggregate_product_info.vendor_code', '=', 'cp.vendor_code')
            ->join('card_products', 'card_products.vendor_code', '=', 'cp.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getLastMinPositionCategory('cp', 'cte_web_id', $startDate, $endDate)),
                'method_last_position_category.vendor_code', '=', 'cp.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getBreadcrumbsCategory('method_last_position_category')),
                'method_breadcrumbs_category.web_id', '=', 'method_last_position_category.web_id')
            ->leftJoin(DB::raw(StatisticQueries::getBreadcrumbsCategory('cp', 'breadcrumbs_min_web')),
                'breadcrumbs_min_web.web_id', '=', 'cp.web_id')
            ->leftJoin('brands as b', 'b.brand_id', '=', 'card_products.brand_id')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'card_products.suppliers_id')
            ->leftJoin(DB::raw(StatisticQueries::getProductInfo('cp', $endDate)),
                'method_product_info.vendor_code', '=', 'cp.vendor_code')
            ->leftJoin(DB::raw(StatisticQueries::getStock('cp', $endDate)),
                'method_stock.vendor_code', '=', 'cp.vendor_code');

        return $products;
    }
}