<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Http;

class AnalyticsRequestService
{
    /**
     * @param string $url
     * @param array $data
     * @return array
     */
    public function sendPostRequest(string $url, array $data): array
    {
        $json = Http::withHeaders([
            'Authorization-Web-App' => config('api.analytics_api_token')
        ])
            ->post(config('api.analytics_api_url') . $url, $data)
            ->json();

        return $json;
    }
}
