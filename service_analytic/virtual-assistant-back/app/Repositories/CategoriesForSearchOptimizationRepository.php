<?php

namespace App\Repositories;

use App\Models\OzWbCategory;

class CategoriesForSearchOptimizationRepository
{
    /**
     * Получить список категорий wb, соответствующих категории oz.
     *
     * @param  string|null  $ozCategory
     * @return \Illuminate\Support\Collection
     */
    public function getWbCategoryAnalogOz(string $ozCategory = null)
    {
        $result = OzWbCategory::query();

        if ($ozCategory) {
            $result->where('oz', $ozCategory);
        }

        return $result->pluck('wb');
    }
}
