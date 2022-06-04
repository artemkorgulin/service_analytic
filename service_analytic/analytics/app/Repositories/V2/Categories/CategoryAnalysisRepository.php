<?php

namespace App\Repositories\V2\Categories;

use App\Helpers\CategoryAnalysisHelper;
use App\Models\Clickhouse\CardProduct;
use App\Models\Clickhouse\Product;
use App\Models\Clickhouse\ProductInfo;
use Bavix\LaravelClickHouse\Database\Query\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use stdClass;
use Tinderbox\Clickhouse\Exceptions\ClientException;

class CategoryAnalysisRepository
{
    protected array $brands;
    protected string $startDate;
    protected mixed $endDate;
    protected string $keyCache;

    /**
     * @param  array  $requestParams
     */
    public function __construct(array $requestParams)
    {
        $this->brands = $requestParams['brands'];
        $this->startDate = $requestParams['startDate'];
        $this->endDate = $requestParams['endDate'];
        $this->keyCache = sprintf("%s_%s_%s_%s",
            __CLASS__, serialize($this->brands), $this->startDate, $this->endDate);
    }

    /**
     * @return array
     * @throws ClientException
     */
    public function prepareAnalysis(): array
    {
        $avgData = $this->getAvgAnalysis();
        if (empty($avgData)) {
            return [];
        }

        $data = $this->getAnalysis();
        $fieldsForSchedule = ['sku_count', 'take', 'suppliers_count', 'avg_take'];
        $result = [];

        foreach ($data as $element) {
            if (!isset($result[$element['brand_id']])) {
                $result[$element['brand_id']] = [];
                if (isset($avgData[$element['brand_id']])) {
                    $result[$element['brand_id']]['avg'] = $avgData[$element['brand_id']];
                } else {
                    $result[$element['brand_id']]['avg'] = new StdClass();
                }
                $result[$element['brand_id']]['avg']['avg_take'] = 0;

                $result[$element['brand_id']]['table'] = [];

                $result[$element['brand_id']]['range'] = [
                    'min' => [],
                    'max' => [],
                ];
                foreach ($fieldsForSchedule as $field) {
                    $result[$element['brand_id']]['range']['min'][$field] = $element[$field];
                    $result[$element['brand_id']]['range']['max'][$field] = $element[$field];
                }
            }

            foreach ($fieldsForSchedule as $field) {
                if ($element[$field] < $result[$element['brand_id']]['range']['min'][$field]) {
                    $result[$element['brand_id']]['range']['min'][$field] = $element[$field];
                }
                if ($element[$field] > $result[$element['brand_id']]['range']['max'][$field]) {
                    $result[$element['brand_id']]['range']['max'][$field] = $element[$field];
                }
            }

            $result[$element['brand_id']]['table'][$element['subject_id']] = $element;

            $result[$element['brand_id']]['avg']['avg_take'] += $element['avg_take'];
        }

        //мапы для качественного анализа
        //находим subject_ids для каждого бренда
        $subjectsMap = [];
        $subjects = [];
        $subjectsAvgTake = [];
        foreach ($result as $brand => $statistic) {
            $result[$brand]['avg']['avg_take'] = round($statistic['avg']['avg_take'], 2);
            $subjectsMap[$brand] = array_keys($statistic['table']);

            $subjectsAvgTake[$brand] = [];
            foreach ($statistic['table'] as $el) {
                $subjects[$el['subject_id']] = $el['subject_name'];
                $subjectsAvgTake[$brand][$el['subject_id']] = $el['avg_take'];
            }
        }

        //качественный анализ
        foreach ($result as $brand => $statistic) {
            $result[$brand]['quality'] = [];
            $tempData = [
                'subjects_decrease' => [],
                'subject_content' => [],
            ];

            //расчитываем рекомендацию по увеличению числа категорий
            $tempData['subjects_decrease'] = CategoryAnalysisHelper::getReccomendationsSubjectsDecrease($subjectsMap,
                $subjects, $brand);
            if ($tempData['subjects_decrease']) {
                $result[$brand]['quality']['subjects_decrease'] = $tempData['subjects_decrease'];
            }

            //расчитываем реккомендацию по оптимизации контента по предмету
            $tempData['subject_content'] = CategoryAnalysisHelper::getReccomendationsSubjectContent($statistic['table'],
                $subjects, $brand, $subjectsAvgTake, $result[$brand]['avg']['brand_name']);
            if ($tempData['subject_content']) {
                $result[$brand]['quality']['subject_content'] = $tempData['subject_content'];
            }
        }

        foreach ($avgData as $brand_id => $element) {
            if (!isset($result[$brand_id])) {
                $result[$brand_id] = [];
                $result[$brand_id]['avg'] = $element;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCachedAnalysis(): array
    {
        return Cache::remember(
            md5(sprintf("%s_%s", __METHOD__, $this->keyCache)),
            Carbon::now()->secondsUntilEndOfDay(),
            function () {
                return $this->prepareAnalysis();
            }
        );
    }

    /**
     * @return array
     * @throws ClientException
     */
    public function getAnalysis(): array
    {
        $subQueryByCategory = CardProduct::query()
            ->select([
                'cp.vendor_code',
                'cp.category',
                raw("extract(category, '^.+\/([^\/]+)$') as subject"),
                raw("sum(cp.revenue) as revenue"),
                raw("argMax(cp.suppliers_id, cp.date) as supplier_id"),
                raw("any(cp.brand_id) as brand_id"),
                raw("any(cp.brand_name) as brand_name"),
                raw("argMax(cp.subject_id, cp.date) as subject_id")
            ])
            ->from('card_products', 'cp')
            ->where('category', '!=', '')
            ->whereBetween('cp.date', [$this->startDate, $this->endDate])
            ->whereIn('cp.brand_id', $this->brands)
            ->groupBy('cp.vendor_code', 'cp.category');

        $subQueryBySubject = CardProduct::query()
            ->select([
                'products_by_category.vendor_code',
                'products_by_category.subject_id',
                raw('any(products_by_category.revenue) as revenue'),
                raw('any(products_by_category.supplier_id) as supplier_id'),
                raw('any(products_by_category.brand_id) as brand_id'),
                raw('any(products_by_category.brand_name) as brand_name'),
                raw('any(products_by_category.subject) as subject')
            ])
            ->from($subQueryByCategory->getQuery(), 'products_by_category')
            ->groupBy('products_by_category.vendor_code', 'products_by_category.subject_id');

        return CardProduct::query()
            ->select([
                'result.brand_id',
                'result.subject_id',
                raw('any(result.brand_name) as brand_name'),
                raw('any(result.subject) as subject_name'),
                raw('count(DISTINCT result.vendor_code) as sku_count'),
                raw('sum(result.revenue) as take'),
                raw('count(DISTINCT result.supplier_id) as suppliers_count'),
                raw('intDivOrZero(take, sku_count) as avg_take')
            ])
            ->from($subQueryBySubject->getQuery(), 'result')
            ->groupBy('result.subject_id', 'result.brand_id')
            ->orderBy('take', 'desc')
            ->get()->toArray();
    }

    /**
     * @return array
     * @throws ClientException
     */
    public function getAvgAnalysis(): array
    {
        $subQueryProducts = Product::query()
            ->select([
                'p.vendor_code',
                'p.brand_name',
                'p.brand_id',
                'p.suppliers_id',
                'pi.revenue'
            ])
            ->from($this->subQueryProducts(), 'p')
            ->allLeftJoin($this->subQueryProductInfo(), ['vendor_code'], false, 'pi')
            ->getQuery();

        return Product::query()
            ->select([
                'b.brand_id',
                raw('any(b.brand_name) as brand_name'),
                raw('count(DISTINCT b.vendor_code) as sku_count'),
                raw('count(DISTINCT b.suppliers_id) as suppliers_count'),
                raw('sum(b.revenue) / 1 as take')
            ])
            ->from($subQueryProducts, 'b')
            ->groupBy('b.brand_id')
            ->get()
            ->keyBy('brand_id')->toArray();
    }

    /**
     * @return Builder
     */
    protected function subQueryProducts(): Builder
    {
        return Product::query()->alias('p')
            ->select([
                'p.vendor_code as vendor_code',
                raw('argMax(p.brand, p.date) as brand_name'),
                raw('argMax(p.brand_id, p.date) as brand_id'),
                raw('argMax(p.suppliers_id, p.date) as suppliers_id')
            ])
            ->whereIn('p.brand_id', $this->brands)
            ->groupBy('p.vendor_code')
            ->getQuery();
    }

    /**
     * @return Builder
     */
    protected function subQueryProductInfo(): Builder
    {
        $subQueryWhereInVendorCode = Product::query()->alias('p')
            ->select([
                raw('DISTINCT p.vendor_code')
            ])
            ->whereIn('p.brand_id', $this->brands)
            ->getQuery();

        return ProductInfo::query()
            ->select([
                'pi.vendor_code',
                raw('sum(pi.revenue) / 1 as revenue')
            ])
            ->alias('pi')
            ->whereIn('pi.vendor_code', $subQueryWhereInVendorCode)
            ->whereBetween('pi.date', [$this->startDate, $this->endDate])
            ->groupBy('pi.vendor_code')
            ->getQuery();
    }

}