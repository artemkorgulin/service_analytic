<?php

namespace App\Repositories\V1\Categories;

use App\Helpers\StatisticQueries;
use Illuminate\Support\Facades\DB;

class CategoryStatisticRepository
{
    protected $categoryId;
    protected $subjectId;
    protected $startDate;
    protected $endDate;

    /**
     * @param array $requestParams
     */
    public function __construct(array $requestParams)
    {
        $this->categoryId = $requestParams['categoryId'];
        $this->subjectId = $requestParams['subjectId'];
        $this->startDate = $requestParams['startDate'];
        $this->endDate = $requestParams['endDate'];
    }

    public function getCategories($selectBlock)
    {
        $categoryId = $this->categoryId;
        $subjectId = $this->subjectId;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $category = DB::table('category_trees AS ct')
            ->where('web_id', $categoryId)
            ->first();

        $subCategories = DB::table('category_trees')
            ->where('lft', '>=', $category->lft)
            ->where('rgt', '<=', $category->rgt)
            ->where('depth', '<=', $category->depth + 1)
            ->pluck('web_id');

        if ($subjectId) {
            $result = $this->getDataSubjects($category->web_id, $startDate, $endDate, $subjectId);
        } elseif ($subCategories->count() == 1) {
            $categoryRecord = DB::table('category_trees')->where('web_id', '=', $subCategories[0])->first();
            $subjectIds = json_decode($categoryRecord->subjects);

            if ($subjectIds) {
                $categoryQuery = $this->getDataCateories($subCategories->toArray(), $startDate, $endDate);

                $subjectsQuery = $this->getDataSubjects($category->web_id, $startDate, $endDate);

                $result = $categoryQuery->union($subjectsQuery);
            } else {
                $result = $this->getDataCateories($subCategories->toArray(), $startDate, $endDate);
            }
        } else {
            return 'Для данной категории отчет пока не доступен.';
        }

        $result = DB::query()->fromSub($result, 'b')
            ->select(DB::raw($selectBlock));

        return $result;
    }

    /**
     * @param $categoriesIds
     * @param string $startDate
     * @param string $endDate
     * @return mixed
     */
    private function getDataCateories($categoriesIds, string $startDate, string $endDate)
    {
        $categoryVendors = DB::table('category_trees AS ct')
            ->join('category_trees AS sct', function ($join) use ($categoriesIds) {
                $join->on('ct.lft', '<=', 'sct.lft')
                    ->on('ct.rgt', '>=', 'sct.rgt')
                    ->whereIn('ct.web_id', $categoriesIds);
            })
            ->join('category_vendor AS cv', function ($join) use ($startDate, $endDate) {
                $join->on('cv.web_id', '=', 'sct.web_id')
                    ->where('cv.date', '<=', $endDate);
            })
            ->select('ct.web_id', 'cv.vendor_code')
            ->groupBy('ct.web_id')
            ->groupBy('cv.vendor_code');

        $categoryValues = DB::query()->fromSub($categoryVendors, 'cv')
            ->selectRaw(' cv.web_id AS category_id,
                          AVG(pi.grade) AS rating,
                          AVG(pi.comments_count) AS comments,
                          COUNT(DISTINCT cp.vendor_code) AS products,
                          COUNT(DISTINCT cp.vendor_code) filter (where pi.current_sales > 0) AS products_with_sale,
                          SUM(pi.current_sales) AS sales,
                          AVG(pi.sale_price_u) AS avg_price,
                          SUM(pi.revenue) AS take,
                          COUNT(DISTINCT cp.suppliers_id) AS count_suppliers,
                          COUNT(DISTINCT cp.suppliers_id) filter (where pi.current_sales > 0) AS count_suppliers_with_sale,
                          COUNT(DISTINCT cp.brand_id) AS count_brands,
                          COUNT(DISTINCT cp.brand_id) filter (where pi.current_sales > 0) AS count_brands_with_sale
                       ')
            ->join('card_products AS cp', function ($join) {
                $join->on('cv.vendor_code', '=', 'cp.vendor_code');
            })
            ->join('product_info AS pi', function ($join) use ($startDate, $endDate) {
                $join->on('pi.vendor_code', '=', 'cv.vendor_code')
                    ->where('pi.date', '>=', $startDate)
                    ->where('pi.date', '<=', $endDate);
            });

        $categoryValues->groupBy('cv.web_id');

        $result = DB::query()->fromSub($categoryValues, 'a')
            ->selectRaw('a.*, ct.name AS rubric')
            ->join('category_trees AS ct', function ($join) {
                $join->on('ct.web_id', '=', 'a.category_id');
            });

        return $result;
    }

    /**
     * @param int $categoryId
     * @param string $startDate
     * @param string $endDate
     * @param int|null $subjectId
     * @return mixed
     */
    private function getDataSubjects(int $categoryId, string $startDate, string $endDate, int $subjectId = null)
    {
        $query = DB::table('category_vendor as cv')
            ->selectRaw('   cv.subject_id,
                                      MAX(cv.web_id) as web_id,
                                      AVG(pi.grade) AS rating,
                                      AVG(pi.comments_count) AS comments,
                                      COUNT(DISTINCT cp.vendor_code) AS products,
                                      COUNT(DISTINCT cp.vendor_code) filter (where pi.current_sales > 0) AS products_with_sale,
                                      SUM(pi.current_sales) AS sales,
                                      AVG(pi.sale_price_u) AS avg_price,
                                      SUM(pi.revenue) AS take,
                                      COUNT(DISTINCT cp.suppliers_id) AS count_suppliers,
                                      COUNT(DISTINCT cp.suppliers_id) filter (where pi.current_sales > 0) AS count_suppliers_with_sale,
                                      COUNT(DISTINCT cp.brand_id) AS count_brands,
                                      COUNT(DISTINCT cp.brand_id) filter (where pi.current_sales > 0) AS count_brands_with_sale
                          ')
        ->where('cv.date', '<=', $endDate)
        ->where('cv.web_id', $categoryId);

        if ($subjectId) {
            $query->where('cv.subject_id', $subjectId);
        }

        $query->join('card_products AS cp', function ($join) {
                $join->on('cv.vendor_code', '=', 'cp.vendor_code');
            })
            ->join('product_info AS pi', function ($join) use ($startDate, $endDate) {
                $join->on('pi.vendor_code', '=', 'cv.vendor_code')
                    ->where('pi.date', '>=', $startDate)
                    ->where('pi.date', '<=', $endDate);
            })
            ->groupBy('cv.subject_id');

        $result = DB::query()->fromSub($query, 'a')
            ->selectRaw('
                            a.subject_id AS category_id,
                            a.rating,
                            a.comments,
                            a.products,
                            a.products_with_sale,
                            a.sales,
                            a.avg_price,
                            a.take,
                            a.count_suppliers,
                            a.count_suppliers_with_sale,
                            a.count_brands,
                            a.count_brands_with_sale,
                            method_breadcrumbs_category.subject AS rubric
                        ')
            ->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('a')),
                'a.web_id', '=', 'method_breadcrumbs_category.web_id');

        return $result;
    }
}
