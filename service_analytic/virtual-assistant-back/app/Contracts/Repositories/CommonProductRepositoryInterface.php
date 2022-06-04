<?php

namespace App\Contracts\Repositories;

use App\Models\ProductDashboard;
use Illuminate\Database\Eloquent\Builder;

interface CommonProductRepositoryInterface
{
    /**
     * @param ProductDashboard $dashboard
     * @param string $segmentType
     * @param string|null $sortBy
     * @param string|null $sortType
     * @return Builder
     */
    public function getProductQueryByDashboardSegmentation(
        ProductDashboard $dashboard,
        string $segmentType,
        ?string $sortBy = '',
        ?string $sortType = ''
    ): Builder;

    /**
     * @param array $ids
     * @param string|null $sortBy
     * @param string|null $sortType
     * @return Builder
     */
    public function getProductQueryByIds(
        array $ids,
        ?string $sortBy = '',
        ?string $sortType = ''
    ): Builder;
}
