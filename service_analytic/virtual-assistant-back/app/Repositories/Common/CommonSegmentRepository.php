<?php

namespace App\Repositories\Common;

use App\Models\OzProduct;
use App\Models\OzProductAnalyticsData;
use App\Services\Common\AccountQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class CommonSegmentRepository
{
    const DATE_OFFSET = 30;
    const SEGMENT_OPTIMIZATION_GOOD = [80, 100];
    const SEGMENT_OPTIMIZATION_NORMAL = [40, 79];
    const SEGMENT_OPTIMIZATION_BAD = [0, 39];

    const DASHBOARD_PRODUCT_OPTIMIZATION = 'product_optimization';
    const DASHBOARD_CATEGORY_OPTIMIZATION = 'category_optimization';
    const DASHBOARD_BRAND_OPTIMIZATION = 'brand_optimization';

    const DASHBOARD_PRODUCT_REVENUE = 'product_revenue';
    const DASHBOARD_CATEGORY_REVENUE = 'category_revenue';
    const DASHBOARD_BRAND_REVENUE = 'brand_revenue';

    const DASHBOARD_PRODUCT_ORDERED = 'product_ordered';
    const DASHBOARD_CATEGORY_ORDERED = 'category_ordered';
    const DASHBOARD_BRAND_ORDERED = 'brand_ordered';

    protected ?string $dateFrom;
    protected ?string $dateTo;

    /**
     * @param  OzProduct  $ozProduct
     * @param  OzProductAnalyticsData  $analyticsData
     */
    public function __construct(
        protected int $userId,
        protected int $accountId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->dateFrom = $dateFrom ?? now()->subDays(self::DATE_OFFSET)->format('Y-m-d');
        $this->dateTo = $dateTo ?? now()->format('Y-m-d');

        $this->initObjects();
    }

    /**
     * @param  string  $dashboardType
     * @return array
     */
    public function getDataByDashboardType(string $dashboardType): array
    {
        if (!in_array($dashboardType, array_keys(config('model.dashboard.type')))) {
            throw new \Exception('Error dashboard type.');
        }

        $method = config('model.dashboard.type.' . $dashboardType . '.method');

        return $this->$method();
    }

    /**
     * @param string $tableName
     * @param string|null $as
     * @return Builder
     * @throws \Exception
     */
    protected function initAccount(string $productTable, string $relationsTable, string $relationsField): Builder
    {
        $accountService = new AccountQueryService();

        return $accountService->initDataManager($productTable)
            ->setUserAccount(
                $this->userId,
                $this->accountId,
                $relationsTable,
                $relationsField
            )
            ->getQueryBuilder();
    }

    /**
     * @param Collection $collectionSegment
     * @return array
     * @throws \Exception
     */
    protected function prepareReturnOptimizationData(Collection $collectionSegment): array
    {
        $segments = $collectionSegment->first();
        $total = $segments->bad + $segments->normal + $segments->good;

        if (!$total) {
            return $this->getEmptyOptimization();
        }

        return [
            'bad' => [
                'percent' => round($segments->bad / $total * 100, 2),
                'count' => $segments->bad,
                'optimization' => self::SEGMENT_OPTIMIZATION_BAD,
            ],
            'normal' => [
                'percent' => round($segments->normal / $total * 100, 2),
                'count' => $segments->normal,
                'optimization' => self::SEGMENT_OPTIMIZATION_NORMAL,
            ],
            'good' => [
                'percent' => round($segments->good / $total * 100, 2),
                'count' => $segments->good,
                'optimization' => self::SEGMENT_OPTIMIZATION_GOOD,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getEmptyOptimization(): array
    {
        return [
            'bad' => [
                'count' => 0,
                'optimization' => self::SEGMENT_OPTIMIZATION_BAD,
            ],
            'normal' => [
                'count' => 0,
                'optimization' => self::SEGMENT_OPTIMIZATION_NORMAL,
            ],
            'good' => [
                'count' => 0,
                'optimization' => self::SEGMENT_OPTIMIZATION_GOOD,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getEmptyOrderedAndAmount(): array
    {
        return [
            'bad' => [
                'count' => 0,
            ],
            'normal' => [
                'count' => 0,
            ],
            'good' => [
                'count' => 0,
            ],
        ];
    }

    /**
     * @param string $type
     * @return array
     */
    public static function getSermentationOptimizationRangeByType(string $type): array
   {
        return constant('self::SEGMENT_OPTIMIZATION_' . strtoupper($type));
   }


}
