<?php

namespace App\Http\Controllers;

use App\Models\OzProductAnalyticsData;
use Illuminate\Http\Request;

class OzProductAnalyticsDataController extends Controller
{
    /**
     * Функция возвращает аналитические данные по товарам Ozon
     * задача SE-74
     * @param Request $request
     */
    public function getAnalyticsData(Request $request) {
        $product_id = $request->get('product_id');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        return response()->json(
            OzProductAnalyticsData::where('product_id', $product_id)->whereBetween($date_from, $date_to)->get()
        );
    }
}
