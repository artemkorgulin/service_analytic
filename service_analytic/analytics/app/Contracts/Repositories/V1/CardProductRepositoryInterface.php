<?php

namespace App\Contracts\Repositories\V1;

use App\Models\CardProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface CardProductRepositoryInterface
{
    public function getDetailStatistic($vendorCode, $request): CardProduct|Model|null;

    public function getRecommendations($vendorCode): \Staudenmeir\LaravelCte\Query\Builder;

    public function getRatingList(array $vendorCode): Collection;

    public function firstByVendorCode(int $vendorCode): CardProduct|Builder|null;
}
