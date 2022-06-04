<?php

namespace App\Services\Analytics;

use App\Services\Analytics\Interfaces\WbAnalyticsServiceInterface;
use Illuminate\Support\Facades\Http;

class WbAnalyticsService implements WbAnalyticsServiceInterface
{

    /**
     * Get ratings for wb product id array
     * @param $productIds
     * @return array|null
     */
    function getProductsRating($productIds): array|null
    {
        $json = Http::withHeaders([
            'Authorization-Web-App' => config('api.analytics_api_token')
        ])
            ->get(config('api.analytics_api_url') . '/wb/get/ratings', ['vendor_code' => $productIds])
            ->json();
        return $json;
    }
}
