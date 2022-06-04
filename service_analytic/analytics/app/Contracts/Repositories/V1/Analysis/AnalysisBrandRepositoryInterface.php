<?php

namespace App\Contracts\Repositories\V1\Analysis;

use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface AnalysisBrandRepositoryInterface
{
    public function findByBrandId(int $brandId): Brand|ModelNotFoundException;

    public function getAnalysis(int $brandId, string $startDate, string $endDate, $selectBlock = null): mixed;
}
