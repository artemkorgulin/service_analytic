<?php

namespace App\Contracts\Repositories\V1\Categories;

use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\LaravelCte\Query\Builder as BuilderCte;

interface CategoryProductRepositoryInterface
{
    public function getProductsByCategoryFilters(string $selectBlock): Builder|BuilderCte;
}