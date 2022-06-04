<?php

namespace App\Contracts\Repositories\V1;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    const DEFAULT_DATE_OFFSET = 30;

    /**
     * @return Collection
     */
    public function productRevenuePercent(): array;

    /**
     * @return Collection
     */
    public function categoryRevenuePercent(): array;

    /**
     * @return Collection
     */
    public function brandRevenuePercent(): array;

    /**
     * @return Collection
     */
    public function productOrderedPercent(): array;

    /**
     * @return Collection
     */
    public function categoryOrderedPercent(): array;

    /**
     * @return Collection
     */
    public function brandOrderedPercent(): array;
}
