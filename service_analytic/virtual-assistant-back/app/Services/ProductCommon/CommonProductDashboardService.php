<?php

namespace App\Services\ProductCommon;

use App\DTO\Common\CommonProductDashboardCreateDTO;
use App\DTO\Common\CommonProductDashboardUpdateDTO;
use App\Models\ProductDashboard;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Contracts\Repositories\SegmentationRepositoryInterface;

class CommonProductDashboardService
{
    /**
     * @param int $userId
     * @param int $accountId
     * @param int $marketplaceId
     */
    public function __construct(
        private int $userId,
        private int $accountId,
        private int $marketplaceId
    ) {
        $this->dashboardRepository = new CommonProductDashboardRepository();
    }

    /**
     * @param CommonProductDashboardCreateDTO $productDashboardCreateDTO
     * @return void
     */
    public function storeDashboard(CommonProductDashboardCreateDTO $productDashboardCreateDTO): void
    {
        ProductDashboard::create($productDashboardCreateDTO->toArray());
    }

    /**
     * @param ProductDashboard $dashboard
     * @param CommonProductDashboardUpdateDTO $productUpdateDTO
     * @return void
     */
    public function updateDashboard(
        ProductDashboard $dashboard,
        CommonProductDashboardUpdateDTO $productUpdateDTO
    ): void {
        $dashboard->update([
            'good_segment' => $productUpdateDTO->getGoodSegment(),
            'normal_segment' => $productUpdateDTO->getNormalSegment(),
            'bad_segment' => $productUpdateDTO->getBadSegment(),
        ]);
    }

    /**
     * @param string $dashboardType
     * @return bool
     * @throws \Exception
     */
    public function updateOrCreateDashboard(
        string $dashboardType
    ): bool {
        $dashboard = $this->dashboardRepository->getAccountDashboardByType(
            $this->userId,
            $this->accountId,
            $this->marketplaceId,
            $dashboardType
        );

        $segmentRepository = $this->initSegmentRepositoriesClass();
        $segmentationData = $segmentRepository->getDataByDashboardType($dashboardType);

        if (!$dashboard || !$dashboard->exists()) {

            $createDTO = new CommonProductDashboardCreateDTO(
                $this->userId,
                $this->accountId,
                $this->marketplaceId,
                $dashboardType,
                $segmentationData
            );

            $this->storeDashboard($createDTO);

            return true;
        }

        $updateDTO = new CommonProductDashboardUpdateDTO($segmentationData);
        $this->updateDashboard($dashboard, $updateDTO);

        return true;
    }

    /**
     * @return SegmentationRepositoryInterface
     * @throws \Exception
     */
    protected function initSegmentRepositoriesClass(): SegmentationRepositoryInterface
    {
        $segmentClassKey = array_search(
            $this->marketplaceId,
            array_column(config('model.dashboard.repositories'), 'marketplace_id'));

        $segmentClass = config('model.dashboard.repositories.' . $segmentClassKey . '.segmentation_class');

        if (!$segmentClass) {
            throw new \Exception('Отсутствует класс repositorySegment для данного маркетплейса id ' . $this->marketplaceId);
        }

        return new $segmentClass($this->userId, $this->accountId);
    }

}
