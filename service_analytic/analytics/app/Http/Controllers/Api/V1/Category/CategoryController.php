<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Helpers\QueryConditionCategoryProduct;
use App\Helpers\RequestParams\Categories\CategoryProductParams;
use App\Helpers\WildberriesHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CardProduct\CardProductFilterRequest;
use App\Repositories\V1\Categories\CategoryProductRepository;
use App\Services\GraphForProductService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Exception;

class CategoryController extends Controller
{

    /**
     * @param  CardProductFilterRequest  $request
     * @param  CategoryProductParams  $requestParams
     * @return mixed
     */
    public function getProducts(CardProductFilterRequest $request, CategoryProductParams $requestParams): mixed
    {
        try {
            $requestParams = $requestParams->getRequestParams($request);
            $categoryProductRepository = new CategoryProductRepository($requestParams);
            $queryCondition = new QueryConditionCategoryProduct();
            $selectBlock = $queryCondition->getSelectParams();
            $products = $categoryProductRepository->getProductsByCategoryFilters($selectBlock);
            // TODO: перенести логику в репозиторий
            $queryCondition->prepare($products, $requestParams['filters']);

            if ($requestParams['sort']) {
                $products->orderBy($requestParams['sort']['col'], $requestParams['sort']['sort']);
            } else {
                $products->orderBy('revenue', 'desc');
            }

            $products = PaginatorHelper::addPagination($request, $products);

            if ($products->count() > 0) {
                $resultVendorCode = $products->pluck('sku');
                $productsGraph = GraphForProductService::graphsForReport(
                    $resultVendorCode,
                    $requestParams['startDate'],
                    $requestParams['endDate']
                );

                foreach ($products as &$product) {
                    $wbImages = WildberriesHelper::generateWbImagesUrl($product->sku);

                    $product->url = WildberriesHelper::generateProductUrl($product->sku);
                    $product->image = $wbImages['small'];
                    $product->image_middle = $wbImages['middle'];
                    $product->graph_sales = explode(',', $productsGraph[$product->sku]->graph_sales);
                    $product->graph_category_count = explode(',', $productsGraph[$product->sku]->graph_category_count);
                    $product->graph_price = explode(',', $productsGraph[$product->sku]->graph_price);
                    $product->graph_stock = explode(',', $productsGraph[$product->sku]->graph_stock);
                }
            }

            return response()->api_success($products);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
