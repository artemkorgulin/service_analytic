<?php

namespace App\Contracts\Repositories\V1\Categories;

interface CategoryTreeRepositoryInterface
{
    public function getTreeArray(): array;
}
