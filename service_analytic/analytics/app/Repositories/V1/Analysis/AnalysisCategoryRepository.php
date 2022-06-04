<?php

namespace App\Repositories\V1\Analysis;

use App\Contracts\Repositories\V1\Analysis\AnalysisCategoryRepositoryInterface;
use App\Helpers\StatisticQueries;
use App\Models\CategoryVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AnalysisCategoryRepository implements AnalysisCategoryRepositoryInterface
{
    /**
     * @param  int  $subjectId
     * @return CategoryVendor|ModelNotFoundException
     */
    public function findBySubjectId(int $subjectId): CategoryVendor|ModelNotFoundException
    {
        return CategoryVendor::query()->where('subject_id', '=', $subjectId)->firstOrFail();
    }

    /**
     * Получить данные для ценового  анализа.
     * @param  int  $subjectId
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  null  $selectBlock
     * @return mixed
     */
    public function getAnalysis(int $categoryId, int $subjectId, string $startDate, string $endDate, $selectBlock = null): mixed
    {

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
            ->leftJoin('brands as b', 'b.brand_id', '=', 'card_products.brand_id')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'card_products.suppliers_id')
            ->leftJoin(DB::raw(StatisticQueries::getProductInfo('cp', $endDate)),
                'method_product_info.vendor_code', '=', 'cp.vendor_code');

        return  $products;
    }
}
