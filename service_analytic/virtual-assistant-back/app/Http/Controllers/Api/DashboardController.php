<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Common\CommonProductDashboardRequest;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Services\UserService;
use function response;

class DashboardController
{
    public function getDataByDashboardType(
        CommonProductDashboardRequest $dashboardRequest,
        CommonProductDashboardRepository $dashboardRepository
    ) {
        $query = $dashboardRequest->get('query');

        $dashboardData = $dashboardRepository->getAccountDashboardByType(
            UserService::getUserId(),
            UserService::getAccountId(),
            UserService::getCurrentAccountPlatformId(),
            $query['dashboard_type']
        );

        if (!$dashboardData || !$dashboardData->exists()) {
            return response()->api_success($dashboardRepository->getEmptyData());
        }

        return response()->api_success(
            $dashboardRepository->mapDashboardResponse($dashboardData)
        );
    }
}
