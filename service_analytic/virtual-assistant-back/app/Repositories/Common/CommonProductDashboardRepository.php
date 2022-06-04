<?php

namespace App\Repositories\Common;

use App\Models\ProductDashboard;

class CommonProductDashboardRepository
{
    /**
     * @param int $userId
     * @param int $accountId
     * @param int $marketplaceId
     * @param string $dashboardType
     * @return ProductDashboard|null
     */
    public function getAccountDashboardByType(
        int $userId,
        int $accountId,
        int $marketplaceId,
        string $dashboardType
    ): ?ProductDashboard {
        return ProductDashboard::query()
            ->where([
                'user_id' => $userId,
                'account_id' => $accountId,
                'marketplace_platform_id' => $marketplaceId,
                'dashboard_type' => $dashboardType
            ])->first();
    }

    /**
     * @param ProductDashboard $dashboard
     * @return array
     */
    public function mapDashboardResponse(ProductDashboard $dashboard): array
    {
        $good = $dashboard->good_segment;
        $normal = $dashboard->normal_segment;
        $bad = $dashboard->bad_segment;

        if (isset($good['product_ids'])) {
            unset($good['product_ids']);
        }

        if (isset($normal['product_ids'])) {
            unset($normal['product_ids']);
        }

        if (isset($bad['product_ids'])) {
            unset($bad['product_ids']);
        }

        return [
            'good' => $good,
            'normal' => $normal,
            'bad' => $bad
        ];
    }

    /**
     * @return array
     */
    public function getEmptyData(): array
    {
        return [
            'good' => ['count' => 0],
            'normal' => ['count' => 0],
            'bad' => ['count' => 0],
        ];
    }

    /**
     * @TODO create segment mapper class
     * @param string $segmentType
     * @return string
     */
    public static function dashboardDBSegmentMapper(string $segmentType): string
    {
        $segmentNames = [
            'bad' => 'bad_segment',
            'normal' => 'normal_segment',
            'good' => 'good_segment',
        ];

        return $segmentNames[$segmentType];
    }
}
