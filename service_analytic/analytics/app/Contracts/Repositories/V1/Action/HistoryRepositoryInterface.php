<?php

namespace App\Contracts\Repositories\V1\Action;

use App\Models\Static\OzHistoryProduct;
use App\Models\Static\WbHistoryProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface HistoryRepositoryInterface
{

    public function getProductList();

    public function getHistoryTop36(int $vendorCode);

    public function getRecommendationsForTop36(int $vendorCode);

    public function getProductBeforeDate(Collection $products, $product): WbHistoryProduct|OzHistoryProduct|null;

}
