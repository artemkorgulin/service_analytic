<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DashboardGroupVendorCodeRequest;
use App\Http\Requests\V1\DashboardRequest;
use App\Repositories\V1\DashboardRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;

class DashboardController extends Controller
{
    /**
     * @param  DashboardGroupVendorCodeRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function productRevenue(DashboardGroupVendorCodeRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->productRevenuePercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryRevenue(DashboardRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->categoryRevenuePercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function brandRevenue(DashboardRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->brandRevenuePercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardGroupVendorCodeRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function productOrdered(DashboardGroupVendorCodeRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->productOrderedPercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryOrdered(DashboardRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->categoryOrderedPercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardRequest  $dashboardRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function brandOrdered(DashboardRequest $dashboardRequest)
    {
        try {
            $service = $this->getRepository($dashboardRequest);

            return response()->api_success($service->brandOrderedPercent());
        } catch (\Exception $exception) {
            report($exception);

            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param  DashboardRequest  $dashboardRequest
     * @return DashboardRepository|DashboardGroupVendorCodeRequest
     */
    public function getRepository(DashboardRequest|DashboardGroupVendorCodeRequest $dashboardRequest)
    {
        $requestQuery = $dashboardRequest->input('query');

        return new DashboardRepository($requestQuery['product_vendor_codes']);
    }
}
