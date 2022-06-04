<?php

namespace App\Services;

class UserParamsService
{
    const MAP = [
        'api/v1/wb/statistic/brands/products' => 'BrandHandler',
        'api/v1/wb/statistic/brands/categories' => 'BrandHandler',
        'api/v1/wb/statistic/brands/sellers' => 'BrandHandler',
        'api/v2/wb/statistic/brands/products' => 'BrandV2Handler',
        'api/v2/wb/statistic/brands/categories' => 'BrandV2Handler',
        'api/v2/wb/statistic/brands/sellers' => 'BrandV2Handler',
        'api/v1/wb/statistic/category/analysis' => 'CategoryAnalysisHandler',
    ];

    /**
     * @param  string  $method
     * @param  int  $userId
     * @return array
     */
    public function getDefaultParams(string $method, int $userId): array
    {
        if (!isset(self::MAP[$method])) {
            return [];
        }

        $classHandlerName = 'App\Helpers\UserParams\DefaultParams\\'.static::MAP[$method];
        $classHandler = new $classHandlerName();
        return $classHandler->getParams($userId);
    }
}
