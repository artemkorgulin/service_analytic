<?php

namespace App\Repositories\Ozon;

use App\Models\Feature;
use App\Models\OzProduct;
use App\Models\OzProductAnalyticsData;
use App\Models\OzProductUser;
use App\Repositories\Common\CommonSegmentRepository;
use App\Contracts\Repositories\SegmentationRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OzonSegmentationRepository extends CommonSegmentRepository implements SegmentationRepositoryInterface
{
    private OzProduct $ozProduct;
    private OzProductAnalyticsData $analyticsData;
    private OzProductUser $OzProductUser;

    /**
     * @return void
     */
    public function initObjects(): void
    {
        $this->ozProduct = new OzProduct();
        $this->analyticsData = new OzProductAnalyticsData();
        $this->OzProductUser = new OzProductUser();
    }

    /**
     * @param string $productTable
     * @return Builder
     * @throws \Exception
     */
    public function initOzonAccountProductsQuery(string $productTable = null)
    {
        $productTable = $productTable ?? $this->ozProduct->getTable();

        return $this->initAccount(
            $productTable,
            $this->OzProductUser->getTable(),
            $this->OzProductUser::PRODUCT_RELATION_FIELD
        );
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByProducts()
     */
    public function getOptimizationSegmentsByProducts(): array
    {
        $countSegments = $this->initOzonAccountProductsQuery()
            ->selectRaw(
                "sum(IF(optimization
                            BETWEEN " . self::SEGMENT_OPTIMIZATION_BAD[0] . "
                            and " . self::SEGMENT_OPTIMIZATION_BAD[1] . ",1,0)
                            OR optimization IS NULL) as bad, " .
                $this->getProductOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_NORMAL[0],
                    self::SEGMENT_OPTIMIZATION_NORMAL[1],
                    'normal'
                ) . ', ' .
                $this->getProductOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_GOOD[0],
                    self::SEGMENT_OPTIMIZATION_GOOD[1],
                    'good'
                ))
            ->get();

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * @param int $startBetween
     * @param int $endBetween
     * @param string $aliasName
     * @return string
     */
    private function getProductOptimizationSubQuery(int $startBetween, int $endBetween, string $aliasName): string
    {
        return sprintf(
            'SUM(IF(optimization BETWEEN %d AND %d,1,0)) AS %s',
            $startBetween,
            $endBetween,
            $aliasName
        );
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByCategory()
     */
    public function getOptimizationSegmentsByCategory(): array
    {
        $countSegments = DB::table($this->ozProduct->getTable())
            ->selectRaw(
                $this->getCategoryOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_BAD,
                    't',
                    'bad'
                ) . "," .
                $this->getCategoryOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_NORMAL,
                    't',
                    'normal'
                ) . "," .
                $this->getCategoryOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_GOOD,
                    't',
                    'good'
                )
            )
            ->fromRaw('dual')
            ->get();

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * @param array $optimizationRange
     * @param string $firstAlias
     * @param string $secondAlias
     * @return string
     */
    private function getCategoryOptimizationSubQuery(array $optimizationRange, string $firstAlias, string $secondAlias)
    {
        return sprintf(
            "(SELECT COUNT(1) FROM(
                         SELECT category_id
                         FROM oz_products
                         WHERE EXISTS (SELECT external_id FROM oz_product_user
                         WHERE user_id = %d AND account_id = %d AND deleted_at IS NULL
                         AND oz_product_user.external_id = oz_products.external_id)
                         AND deleted_at IS NULL
                         GROUP BY category_id
                         HAVING AVG(optimization) BETWEEN %d AND %d) AS %s) AS %s",
            $this->userId,
            $this->accountId,
            $optimizationRange[0],
            $optimizationRange[1],
            $firstAlias,
            $secondAlias
        );
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOptimizationSegmentsByBrand()
     */
    public function getOptimizationSegmentsByBrand(): array
    {
        $countSegments = DB::table($this->ozProduct->getTable())
            ->selectRaw(
                $this->getBrandOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_BAD,
                    't',
                    'bad'
                ) . "," .
                $this->getBrandOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_NORMAL,
                    't2',
                    'normal'
                ) . "," .
                $this->getBrandOptimizationSubQuery(
                    self::SEGMENT_OPTIMIZATION_GOOD,
                    't3',
                    'good'
                )
            )
            ->fromRaw('dual')
            ->get();

        return $this->prepareReturnOptimizationData($countSegments);
    }

    /**
     * @param array $optimizationRange
     * @param string $firstAlias
     * @param string $secondAlias
     * @return string
     */
    private function getBrandOptimizationSubQuery(array $optimizationRange, string $firstAlias, string $secondAlias)
    {
        return sprintf(
            "(SELECT COUNT(1) FROM (
                        SELECT brands.option_id
                        FROM oz_products as product
                        LEFT JOIN (SELECT feature_id, product_id, option_id
                            FROM oz_products_features
                            WHERE feature_id = %d) AS brands
                        ON product.id = brands.product_id
                        WHERE EXISTS
                        (SELECT external_id FROM oz_product_user
                         WHERE user_id = %d AND account_id = %d AND deleted_at IS NULL
                         AND oz_product_user.external_id = product.external_id)
                        AND deleted_at IS NULL
                        GROUP BY brands.option_id
                        HAVING AVG(product.optimization) BETWEEN %d AND %d) as %s) as %s",
            Feature::BRAND_ID,
            $this->userId,
            $this->accountId,
            $optimizationRange[0],
            $optimizationRange[1],
            $firstAlias,
            $secondAlias
        );
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByProducts()
     */
    public function getRevenueSegmentsByProducts(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf('id as product_id, IFNULL(summary, 0) as summary, %s.external_id,
                                     IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent', $productTable))
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'revenue');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy($productTable . '.external_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByCategory()
     */
    public function getRevenueSegmentsByCategory(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf("category_id, IFNULL(summary, 0) as summary, %s.external_id,
                                    GROUP_CONCAT(DISTINCT %s.id) AS product_id,
                                    IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent",
                    $productTable,
                    $productTable
                )
            )
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'revenue');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy('category_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getRevenueSegmentsByBrand()
     */
    public function getRevenueSegmentsByBrand(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf("brand.option_id, %s.external_id,
                                   GROUP_CONCAT(DISTINCT %s.id) AS product_id,
                                   IFNULL(summary, 0) as summary,
                                   IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent",
                    $productTable,
                    $productTable
                )
            )
            ->leftJoinSub(
                "SELECT option_id, product_id FROM  oz_products_features
                       WHERE feature_id = " . Feature::BRAND_ID,
                'brand',
                $productTable . '.id',
                '=',
                'brand.product_id'
            )
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'revenue');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy('brand.option_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByProducts()
     */
    public function getOrderedSegmentsByProducts(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf('id as product_id, IFNULL(summary, 0) as summary, %s.external_id,
                                     IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent', $productTable))
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'ordered_units');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy($productTable . '.external_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByCategory()
     */
    public function getOrderedSegmentsByCategory(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf("category_id, IFNULL(summary, 0) as summary, %s.external_id,
                                    GROUP_CONCAT(DISTINCT %s.id) AS product_id,
                                    IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent",
                    $productTable,
                    $productTable
                )
            )
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'ordered_units');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy('category_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * {@inheritDoc}
     * @see SegmentationRepositoryInterface::getOrderedSegmentsByBrand()
     */
    public function getOrderedSegmentsByBrand(): array
    {
        $productTable = $this->ozProduct->getTable();

        $revenueProducts = $this->initOzonAccountProductsQuery($productTable)
            ->selectRaw(sprintf("brand.option_id, %s.external_id,
                                   GROUP_CONCAT(DISTINCT %s.id) AS product_id,
                                   IFNULL(summary, 0) as summary,
                                   IFNULL(summary * 100 / SUM(summary)  OVER (), 0) as percent",
                                   $productTable,
                                   $productTable
                )
            )
            ->leftJoinSub(
                "SELECT option_id, product_id FROM  oz_products_features
                       WHERE feature_id = " . Feature::BRAND_ID,
                'brand',
                $productTable . '.id',
                '=',
                'brand.product_id'
            )
            ->leftJoinSub(function ($query) {
                $this->getAnalyticaQuery($query, 'ordered_units');
            },
                'analytica',
                'analytica.external_id',
                $productTable . '.external_id'
            )
            ->groupBy('brand.option_id')
            ->orderBy('percent', 'desc');

        return $this->calculatePercentSegmentation($revenueProducts->get());
    }

    /**
     * @param Builder $query
     * @param $fieldCalculation
     * @return Builder
     */
    public function getAnalyticaQuery(Builder $query, $fieldCalculation): Builder
    {
        return $query->selectRaw(sprintf('external_id, SUM(%s) AS summary', $fieldCalculation))
            ->from($this->analyticsData->getTable())
            ->whereBetween('report_date', [$this->dateFrom, $this->dateTo])
            ->groupBy('external_id');
    }

    /**
     * @TODO Need more case for actual logic and do it more simple
     * @param Collection $productPercents
     * @return array
     */
    public function calculatePercentSegmentation(Collection $productPercents): array
    {
        $segmentArray = [];

        $segmentGoodPercent = 0;
        $segmentGoodFull = false;
        $segmentArray['good']['count'] = 0;

        $segmentNormalPercent = 0;
        $segmentNormalFull = false;
        $segmentArray['normal']['count'] = 0;

        $segmentBadPercent = 0;
        $segmentArray['bad']['count'] = 0;

        foreach ($productPercents as $product) {

            $productPercent = $product->percent ? round($product->percent, 2) : 0;

            if (($segmentGoodPercent + $productPercent) < 80 and $segmentGoodFull === false) {
                $segmentGoodPercent += $productPercent;
                $segmentArray['good']['product_ids'] = array_merge(
                    $segmentArray['good']['product_ids'] ?? [],
                    explode(',', $product->product_id)
                );

                $segmentArray['good']['count'] += 1;
                continue;
            }

            $segmentGoodFull = true;

            if ($segmentGoodPercent + $segmentNormalPercent + $productPercent < 96
                and $segmentNormalFull === false) {
                $segmentNormalPercent += $productPercent;
                $segmentArray['normal']['product_ids'] = array_merge(
                    $segmentArray['normal']['product_ids'] ?? [],
                    explode(',', $product->product_id)
                );
                $segmentArray['normal']['count'] += 1;
                continue;
            }

            $segmentNormalFull = true;

            $segmentBadPercent += $productPercent;
            $segmentArray['bad']['product_ids'] = array_merge(
                $segmentArray['bad']['product_ids'] ?? [],
                explode(',', $product->product_id)
            );
            $segmentArray['bad']['count'] += 1;
        }

        if ($segmentGoodPercent) {
            $segmentArray['good']['percent'] = $segmentGoodPercent;
        }

        if ($segmentNormalPercent) {
            $segmentArray['normal']['percent'] = $segmentNormalPercent;
        }

        if ($segmentBadPercent) {
            $segmentArray['bad']['percent'] = $segmentBadPercent;
        }

        return $segmentArray;
    }
}
