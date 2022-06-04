<?php

namespace App\Repositories\Wildberries;

use App\Models\WbProductUser;
use App\Models\WbProduct;
use App\Repositories\Common\CommonSegmentRepository;
use App\Contracts\Repositories\SegmentationRepositoryInterface;
use App\Services\Analytics\AnalyticsRequestService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WildberriesSegmentationRepository extends CommonSegmentRepository implements SegmentationRepositoryInterface
{
    private WbProduct $wbProduct;
    private AnalyticsRequestService $analyticsService;
    private WbProductUser $WbProductUser;
    private WildberriesCategoryRepository $wbCategory;
    private WildberriesBrandRepository $wbBrand;

    /**
     * @return void
     */
    public function initObjects(): void
    {
        $this->wbProduct = new WbProduct();
        $this->analyticsService = new AnalyticsRequestService();
        $this->WbProductUser = new WbProductUser();
        $this->wbCategory = new WildberriesCategoryRepository();
        $this->wbBrand = new WildberriesBrandRepository();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     * @throws \Exception
     */
    protected function initWbAccount()
    {
        return $this->initAccount(
            $this->wbProduct->getTable(),
            $this->WbProductUser->getTable(),
            $this->WbProductUser::PRODUCT_RELATION_FIELD
        );
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByProducts()
     */
    public function getOptimizationSegmentsByProducts(): array
    {
        $countSegments = $this->initWbAccount()
            ->selectRaw(
                "sum(IF(optimization BETWEEN " . self::SEGMENT_OPTIMIZATION_BAD[0] . "
                           and " . self::SEGMENT_OPTIMIZATION_BAD[1] . ",1,0) OR optimization IS NULL) as bad,
                           sum(IF(optimization BETWEEN " . self::SEGMENT_OPTIMIZATION_NORMAL[0] . "
                           and " . self::SEGMENT_OPTIMIZATION_NORMAL[1] . ",1,0)) as normal,
                           sum(IF(optimization BETWEEN " . self::SEGMENT_OPTIMIZATION_GOOD[0] . "
                           and " . self::SEGMENT_OPTIMIZATION_GOOD[1] . ",1,0)) as good")
            ->whereNull('deleted_at')
            ->get();

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByProducts()
     */
    public function getOptimizationSegmentsByCategory(): array
    {
        $countSegments = $this->getSegmentationByProductField('object');

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByProducts()
     */
    public function getOptimizationSegmentsByBrand(): array
    {
        $countSegments = $this->getSegmentationByProductField('brand');

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * @param string $field
     * @return Collection
     */
    public function getSegmentationByProductField(string $field): Collection
    {
        return DB::table($this->wbProduct->getTable())
            ->selectRaw(
                "(SELECT COUNT(1) FROM (
                            " . $this->getSubQuerySegmentation(
                    $field,
                    self::SEGMENT_OPTIMIZATION_BAD
                ) . ") AS t) AS bad,
                            (SELECT COUNT(1) FROM (
                            " . $this->getSubQuerySegmentation(
                    $field,
                    self::SEGMENT_OPTIMIZATION_NORMAL
                ) . ") as t2) as normal,
                            (SELECT COUNT(1) FROM (
                            " . $this->getSubQuerySegmentation(
                    $field,
                    self::SEGMENT_OPTIMIZATION_GOOD
                ) . ")as t3) as good"
            )
            ->fromRaw('dual')
            ->get();
    }

    public function getSubQuerySegmentation(string $field, array $optimization)
    {
        $productTable = $this->wbProduct->getTable();
        $userTable = $this->WbProductUser->getTable();
        $relationField = $this->WbProductUser::PRODUCT_RELATION_FIELD;

        return "
            SELECT {$field}
            FROM wb_products
            WHERE EXISTS (SELECT " . $relationField . "
                          FROM " . $userTable . "
                          WHERE user_id = {$this->userId} AND account_id = {$this->accountId}
                          AND deleted_at IS NULL
                          AND " . $userTable . "." . $relationField . " = " . $productTable . "." . $relationField . ")
            AND deleted_at IS NULL
            GROUP BY {$field}
            HAVING AVG(optimization) BETWEEN " . $optimization[0] . "
                AND " . $optimization[1] . "
        ";
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByProducts()
     */
    public function getRevenueSegmentsByProducts(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_PRODUCT_REVENUE, true);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByCategory()
     */
    public function getRevenueSegmentsByCategory(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_CATEGORY_REVENUE);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByBrand()
     */
    public function getRevenueSegmentsByBrand(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_BRAND_REVENUE);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByProducts()
     */
    public function getOrderedSegmentsByProducts(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_PRODUCT_ORDERED, true);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByCategory()
     */
    public function getOrderedSegmentsByCategory(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_CATEGORY_ORDERED);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByBrand()
     */
    public function getOrderedSegmentsByBrand(): array
    {
        return $this->getSegmentsFromAnalytics(self::DASHBOARD_BRAND_ORDERED);
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getSegmentsFromAnalytics()
     */
    public function getSegmentsFromAnalytics(string $dashboardType, bool $groupByNmid = false): array
    {
        if ($groupByNmid) {
            $articles = $this->getGroupProductArticles();
        } else {
            $articles = $this->getProductArticles();
        }

        $url = config('model.dashboard.type.' . $dashboardType . '.analytics_url');

        if (!$articles) {
            return CommonSegmentRepository::getEmptyOrderedAndAmount();
        }

        $responseAnalytics = $this->analyticsService->sendPostRequest(
            $url,
            $this->prepareRequest($articles)
        );

        if (!isset($responseAnalytics['data'])) {
            $this->errorException($responseAnalytics);
        }

        $readyResponse = $this->fillMissingData($responseAnalytics['data'], $articles, $dashboardType, $groupByNmid);

        return $this->prepareResponseData($readyResponse);
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getProductArticles(): ?array
    {
        return $this->initWbAccount()
            ->select('data_nomenclatures')
            ->whereNotNull('data_nomenclatures')
            ->whereNull('deleted_at')
            ->pluck('data_nomenclatures')
            ->map(function ($item) {
                $result = [];
                foreach (json_decode($item, true) as $nmid) {
                    $result[] = $nmid['nmId'];
                }

                return $result;
            })->collapse()
            ->toArray();
    }

    public function getGroupProductArticles()
    {
        return $this->initWbAccount()
            ->select('data_nomenclatures', 'nmid')
            ->whereNotNull('data_nomenclatures')
            ->whereNull('deleted_at')
            ->pluck('data_nomenclatures', 'nmid')
            ->map(function ($item) {
                $result = [];
                foreach (json_decode($item, true) as $nmid) {
                    $result[] = $nmid['nmId'];
                }

                return $result;
            })->toArray();
    }

    /**
     * @param array $productArticles
     * @return array
     */
    protected function prepareRequest(array $productArticles): array
    {
        return [
            'query' => [
                'product_vendor_codes' => $productArticles
            ]
        ];
    }

    /**
     * @param array $response
     * @return array
     */
    protected function prepareResponseData(array $response): array
    {
        foreach ($response as $key => $segment) {
            foreach ($segment as $keySegment => $value) {
                if ($keySegment === 'vendor_codes') {
                    $response[$key]['product_ids'] = $this->convertArticlesToIds($value);
                    unset($response[$key]['vendor_codes']);
                }
            }
        }

        return $response;
    }

    /**
     * Заполняет ответ 0 данными тех товаров которых нет в аналитике.
     *
     * @param array $response
     * @param array $articles
     * @return array
     */
    public function fillMissingData(array $response, array $articles, $dashboardType, bool $groupByNmid = false): array
    {
        $getResponseArticles = [];

        foreach ($response as $itemSegment) {
            $getResponseArticles = array_merge($getResponseArticles, $itemSegment['vendor_codes'] ?? []);
        }

        if ($groupByNmid) {
            $articles = array_keys($articles);
        }


        if (!$getResponseArticles) {
            $response['bad']['vendor_codes'] = $articles;
            $response['bad']['count'] = count($articles);

            return $response;
        }

        $getDiff = array_diff($articles, $getResponseArticles);

        if (!$getDiff) {
            return $response;
        }

        $response['bad']['vendor_codes'] = array_merge($response['bad']['vendor_codes'] ?? [], $getDiff);

        switch ($dashboardType) {
            case self::DASHBOARD_PRODUCT_REVENUE:
            case self::DASHBOARD_PRODUCT_ORDERED:
                $response['bad']['count'] = $response['bad']['count'] + count($getDiff);
                break;

            case self::DASHBOARD_CATEGORY_REVENUE:
            case self::DASHBOARD_CATEGORY_ORDERED:
                $responseCategoryCount = array_sum(array_column($response, 'count'));
                $requestCategoryCount = $this->wbCategory->countCategoryByProductsArticle($articles);
                $mathCategoryToAdd = $requestCategoryCount - $responseCategoryCount;
                $response['bad']['count'] = $response['bad']['count'] + $mathCategoryToAdd;
                break;

            case self::DASHBOARD_BRAND_REVENUE:
            case self::DASHBOARD_BRAND_ORDERED:
                $responseBrandCount = array_sum(array_column($response, 'count'));
                $requestBrandCount = $this->wbBrand->countBrandByProductsArticle($articles);
                $mathBrandToAdd = $requestBrandCount - $responseBrandCount;
                $response['bad']['count'] = $response['bad']['count'] + $mathBrandToAdd;
                break;
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function convertArticlesToIds(array $data): array
    {
        return DB::table($this->wbProduct->getTable())
            ->select('id')
            ->whereIn('nmid', $data)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    private function errorException($response): mixed
    {
        throw new \Exception('Пустой массив данных с аналитики - ' . json_encode($response, JSON_UNESCAPED_UNICODE));
    }
}
