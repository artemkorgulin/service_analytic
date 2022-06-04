<?php

namespace App\Contracts\Repositories\V1\Analysis;

use App\Models\CategoryVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface AnalysisCategoryRepositoryInterface
{
    public function findBySubjectId(int $subjectId): CategoryVendor|ModelNotFoundException;

    public function getAnalysis(int $categoryId, int $subjectId, string $startDate, string $endDate, $selectBlock = null): mixed;
}
