<?php

namespace App\Http\Controllers\Api\V2\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Product\ProductGetFilteredListRequest;
use App\Http\Requests\V1\Product\ProductSearchBySkuRequest;
use App\Repositories\V2\Product\ProductRepository;
use App\Services\UserService;

class CampaignProductController extends Controller
{
    /**
     * @param ProductGetFilteredListRequest $request
     */
    public function getFilteredList(ProductGetFilteredListRequest $request, ProductRepository $productRepository)
    {
        $productResult = $productRepository->searchProduct($request->search, UserService::getUserId());

        return response()->api_success($productResult);
    }


    /**
     * @param ProductSearchBySkuRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProductsBySku(ProductSearchBySkuRequest $request, ProductRepository $productRepository)
    {
        /** @var array $productsSku */
        $productsSku = $request->input('products_sku');
        $products = [];
        $missedSku = [];
        foreach ($productsSku as $sku) {
            $product = $productRepository->getProducts($sku, UserService::getUserId());
            if ($product) {
                $products[] = $product;
            } else {
                $missedSku[] = $sku;
            }
        }
        return response()->json(
            [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'missedSku' => $missedSku
                ],
                'errors' => [],
            ]
        );
    }
}
