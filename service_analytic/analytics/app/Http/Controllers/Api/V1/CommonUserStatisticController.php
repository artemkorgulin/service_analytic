<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CardProduct;
use App\Repositories\V1\Assistant\WbProductRepository;
use App\Repositories\V1\Assistant\OzProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonUserStatisticController extends Controller
{
    /**
     * @param Request $request
     * @param WbProductRepository $wbProductRepository
     * @param OzProductRepository $ozProductRepository
     * @return JsonResponse
     */
    public function index(Request $request, WbProductRepository $wbProductRepository, OzProductRepository $ozProductRepository): JsonResponse
    {
        $userId = $request->input('user')['id'];

        $countWbProducts = $wbProductRepository->getProductsCount($userId);
        $countOzProducts = $ozProductRepository->getProductsCount($userId);
        $countProducts = $countWbProducts + $countOzProducts;

        $avgRatingProducts = 0;
        $avgOptimizeProducts = 0;
        if ($countProducts) {
            $sumRatingOzProducts = $ozProductRepository->getSumProductField($userId, 'rating');
            $nmids = $wbProductRepository->getUserProductNmids($userId);
            $sumRatingWbProducts = CardProduct::whereIn('vendor_code', $nmids)->sum('grade');
            $avgRatingProducts = ($sumRatingOzProducts + $sumRatingWbProducts) / $countProducts;

            $sumOptimizeOzProducts = $ozProductRepository->getSumProductField($userId, 'optimization');
            $sumOptimizeWbProducts = $wbProductRepository->getSumProductField($userId, 'optimization');
            $avgOptimizeProducts = ($sumOptimizeOzProducts + $sumOptimizeWbProducts) / $countProducts;
        }

        return response()->json([
            'countProducts' => $countProducts,
            'avgRatingProducts' => doubleval(number_format($avgRatingProducts, 1, '.', '')),
            'avgOptimizeProducts' => doubleval(number_format($avgOptimizeProducts, 1, '.', '')),
        ]);
    }
}
