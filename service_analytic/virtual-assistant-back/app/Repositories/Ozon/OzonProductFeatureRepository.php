<?php

namespace App\Repositories\Ozon;

use App\Models\Option;
use App\Models\OzCategory;
use Illuminate\Database\Eloquent\Collection;

class OzonProductFeatureRepository
{
    const CACHE_TTL = 300;

    private Option $option;

    public function __construct()
    {
        $this->option = new Option();
    }

    /**
     * @param int $categoryId
     * @return Collection
     */
    public function getFeaturesCollectionByCategoryId(int $categoryId): Collection
    {
        return optional(OzCategory::query()->where('id', $categoryId)->with('features')->first())->features;
    }

    /**
     * @param int $id
     * @return Option|null
     */
    public function getFeatureListValueById(int $id): ?Option
    {
        return $this->option->query()->select('id', 'value')->find($id);
    }

    /**
     * @param int $id
     * @return  Option|null
     */
    public function getCachedOptionValuesById(int $id): ?Option
    {
        $key = 'value_' . $id;
        return \Cache::remember($key, self::CACHE_TTL, function () use ($id) {
            return $this->getFeatureListValueById($id);
        });

    }
}
