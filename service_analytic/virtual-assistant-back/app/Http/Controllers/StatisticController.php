<?php

namespace App\Http\Controllers;

use App\Models\OzProduct;
use App\Models\ProductPositionHistoryGraph;
use App\Models\WbProduct;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    /**
     * Получить количество товаров wb + oz, средний рейтинг товаров по oz, среднюю степень оптимизации по oz.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductStatistic(Request $request)
    {
        $wbQuery = WbProduct::currentUser();
        $ozQuery = OzProduct::currentUser();
        $countWbProducts = $wbQuery->count();
        $countOzProducts = $ozQuery->count();
        $countProducts = $countWbProducts + $countOzProducts;

        $avgRatingProducts = $ozQuery->avg('rating');
        $avgOptimizeProducts = $ozQuery->avg('optimization');

        return response()->json([
            'countProducts' => $countProducts,
            'avgRatingProducts' => doubleval(number_format($avgRatingProducts, 1, '.', '')),
            'avgOptimizeProducts' => intval($avgOptimizeProducts),
        ]);
    }

    /**
     * Получить топ товаров по позициям, может вернуть макс. 1000.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopPositionProduct(Request $request)
    {
        $userId = $request->input('user')['id'];
        $limit = (int) $request->input('limit');

        $productPositionHistoryGraphs = ProductPositionHistoryGraph::whereHas('product', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orderByDesc('date')->orderBy('position');

        if (!$limit) {
            $limit = 1000;
        }
        $productPositionHistoryGraphs->take($limit);
        $productPositionHistoryGraphs = $productPositionHistoryGraphs->get();

        $maxDate = $productPositionHistoryGraphs->first()?->date;

        $result = [];
        foreach ($productPositionHistoryGraphs as $productPositionHistoryGraph) {
            if ($productPositionHistoryGraph->date == $maxDate) {
                $productPositionHistoryGraphAr = $productPositionHistoryGraph->toArray();
                $productPositionHistoryGraphAr['product_name'] = $productPositionHistoryGraph->product->name;
                $productPositionHistoryGraphAr['photo_url'] = $productPositionHistoryGraph->product->photo_url;
                $productPositionHistoryGraphAr['sku'] = $productPositionHistoryGraph->product->sku;
                $productPositionHistoryGraphAr['optimization'] = $productPositionHistoryGraph->product->optimization;

                $result[] = $productPositionHistoryGraphAr;
            }
        }

        return response()->json($result);
    }
}
