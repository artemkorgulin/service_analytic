<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\QueryConditionBrandCategory;
use App\Helpers\QueryConditionBrandProduct;
use App\Helpers\QueryConditionBrandSupplier;
use App\Helpers\RequestParams\BrandParams;
use App\Helpers\WildberriesHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Brand\ProductFilterRequest;
use App\Models\WbProduct;
use App\Repositories\V1\BrandStatisticRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Exception;

class BrandStatisticController extends Controller
{
    /**
     * @param ProductFilterRequest $request
     * @param BrandParams $requestParams
     * @return mixed
     */
    public function getProducts(ProductFilterRequest $request, BrandParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);

            $brandStatisticRepository = new BrandStatisticRepository($requestParams);

            $queryCondition = new QueryConditionBrandProduct();
            $selectBlock = $queryCondition->getSelectParams();

            $categoryVendors = $brandStatisticRepository->getProducts($selectBlock);

            $queryCondition->prepare($categoryVendors, $requestParams['filters']);

            if ($requestParams['sort']) {
                $categoryVendors->orderBy($requestParams['sort']['col'], $requestParams['sort']['sort']);
            } else {
                $categoryVendors->orderBy('take', 'desc');
            }

            $result = PaginatorHelper::addPagination($request, $categoryVendors);

            //берем массив sku
            $vendorCodes = $result->map(function ($element) {
                return $element->sku;
            })->toArray();

            $allSales = QueryBuilderHelper::getProductSales($vendorCodes, $requestParams['startDate'],
                $requestParams['endDate']);

            $wbProducts = WbProduct::select('id', 'sku', 'content_optimization', 'search_optimization')
                ->whereIn('sku', $vendorCodes)
                ->where('user_id', $requestParams['user']['id'])
                ->get()
                ->keyBy('sku');

            foreach ($result as $element) {
                $element->sales_schedule = '';
                if (isset($allSales[$element->sku])) {
                    $element->sales_schedule = $allSales[$element->sku];
                }

                $element->url = WildberriesHelper::generateProductUrl($element->sku);

                $element->content_optimization = 'none';
                $element->search_optimization = 'none';
                //если товар является нашим
                if ($wbProducts->has($element->sku)) {
                    $element->content_optimization = $wbProducts[$element->sku]->content_optimization;

                    $element->search_optimization = $wbProducts[$element->sku]->search_optimization;
                }
            }

            QueryBuilderHelper::saveUserParams($request,
                ['brand_id', 'start_date', 'end_date', 'filters', 'sort', 'columns', 'columns_order']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param ProductFilterRequest $request
     * @param BrandParams $requestParams
     * @return mixed
     */
    public function getCategories(ProductFilterRequest $request, BrandParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);

            $brandStatisticRepository = new BrandStatisticRepository($requestParams);

            $queryCondition = new QueryConditionBrandCategory();
            $selectBlock = $queryCondition->getSelectParams();

            $categoryVendors = $brandStatisticRepository->getCategories($selectBlock);

            $queryCondition->prepare($categoryVendors, $requestParams['filters']);

            if ($requestParams['sort']) {
                $categoryVendors->orderBy($requestParams['sort']['col'], $requestParams['sort']['sort']);
            } else {
                $categoryVendors->orderBy('take', 'desc');
            }

            $result = PaginatorHelper::addPagination($request, $categoryVendors);

            QueryBuilderHelper::saveUserParams($request,
                ['brand_id', 'start_date', 'end_date', 'filters', 'sort', 'columns', 'columns_order']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param ProductFilterRequest $request
     * @param BrandParams $requestParams
     * @return mixed
     */
    public function getSellers(ProductFilterRequest $request, BrandParams $requestParams)
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);

            $brandStatisticRepository = new BrandStatisticRepository($requestParams);

            $queryCondition = new QueryConditionBrandSupplier();
            $selectBlock = $queryCondition->getSelectParams();

            $categoryVendors = $brandStatisticRepository->getSellers($selectBlock);

            $queryCondition->prepare($categoryVendors, $requestParams['filters']);

            if ($requestParams['sort']) {
                $categoryVendors->orderBy($requestParams['sort']['col'], $requestParams['sort']['sort']);
            } else {
                $categoryVendors->orderBy('take', 'desc');
            }

            $result = PaginatorHelper::addPagination($request, $categoryVendors);

            //берем массив supplier_id
            $supplierIds = $result->map(function ($element) {
                return $element->supplier_id;
            })->toArray();

            $allSales = QueryBuilderHelper::getSupplierSales($supplierIds, $requestParams['brandId'],
                $requestParams['startDate'],
                $requestParams['endDate']);

            foreach ($result as $element) {
                $element->sales_schedule = [];
                if (isset($allSales[$element->supplier_id])) {
                    $element->sales_schedule = $allSales[$element->supplier_id];
                }
            }

            QueryBuilderHelper::saveUserParams($request,
                ['brand_id', 'start_date', 'end_date', 'filters', 'sort', 'columns', 'columns_order']);

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
