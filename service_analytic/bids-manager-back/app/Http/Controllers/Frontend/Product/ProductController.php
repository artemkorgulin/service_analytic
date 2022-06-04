<?php

namespace App\Http\Controllers\Frontend\Product;


use App\Http\Controllers\Controller;
use App\Repositories\V2\Product\ProductRepository;
use App\Http\Requests\V1\Product\ProductGetFilteredListRequest;
use App\Http\Requests\V1\Product\ProductSearchBySkuRequest;
use App\Services\UserService;


class ProductController extends Controller
{
    /**
     * @param ProductGetFilteredListRequest $request
     * @param ProductRepository $productRepository
     * @return mixed
     */
    public function getFilteredList(ProductGetFilteredListRequest $request, ProductRepository $productRepository)
    {
        $productResult = $productRepository->searchProduct($request->search, UserService::getUserId());

        return response()->api_success($productResult);
    }


    /**
     * @param ProductSearchBySkuRequest $request
     * @param ProductRepository $productRepository
     * @return mixed
     */
    public function searchProductsBySku(ProductSearchBySkuRequest $request, ProductRepository $productRepository)
    {
        $productsSku = $request->input('products_sku');
        $products = [];
        $missedSku = [];

        foreach ($productsSku as $sku) {
            $product = $productRepository->getProduct($sku, UserService::getUserId());

            if ($product) {
                $products[] = $product;
            } else {
                $missedSku[] = $sku;
            }
        }

        return response()->api_success(['products' => $products, 'missedSku' => $missedSku]);
    }
}
