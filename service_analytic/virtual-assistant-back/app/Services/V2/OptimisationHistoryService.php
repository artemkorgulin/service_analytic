<?php

namespace App\Services\V2;

use App\Models\OzProduct;
use App\Models\WbProduct;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Support\Facades\DB;

class OptimisationHistoryService
{
    /**
     * Get products optimisation history
     *
     * @param WbProduct|OzProduct $product
     * @return mixed
     */
    public static function productHistory(mixed $product): mixed
    {
        return $product->getOptimisationHistory()
            ->select('content_percent', 'search_percent', 'visibility_percent', 'report_date')
            ->where('account_id', $product->account_id)
            ->distinct('report_date')
            ->latest()
            ->take(30)
            ->get();
    }

    /**
     * Set optimisations to Ozon product
     *
     * @param OzProduct $product
     * @throws \Exception
     */
    public function updateOptimisationToOzon(OzProduct $product)
    {
        $this->updateOptimisation(
            OzProduct::class,
            $product->id,
            OzonOptimizationService::calculateContentOptimization($product),
            OzonOptimizationService::calculateSearchOptimization($product),
            OzonOptimizationService::calculateVisibilityOptimization($product)
        );
    }

    /**
     * Set optimisations to Wildberries product
     *
     * @param WbProduct $product
     * @throws \Exception
     */
    public function updateOptimisationToWildberries(WbProduct $product)
    {
        $this->updateOptimisation(
            WbProduct::class,
            $product->id,
            WbProductServiceUpdater::calculateVisibilityOptimization($product),
            WbProductServiceUpdater::calculateSearchOptimization($product),
            WbProductServiceUpdater::calculateContentOptimization($product)
        );
    }

    /**
     * Set optimisations to product
     *
     * @param mixed $model
     * @param int $id
     * @param int $visibility
     * @param int $search
     * @param int $content
     * @throws \Exception
     */
    public function updateOptimisation(mixed $model, int $id, int $visibility, int $search, int $content)
    {
        $data = [
            'visibility_optimization' => $visibility,
            'search_optimization' => $search,
            'content_optimization' => $content,
            'optimization' => $this->getAverageOptimisation($visibility, $search, $content)
        ];
        ModelHelper::transaction(function () use ($model, $id, $data) {
            $model::where('id', $id)->update($data);
        });
    }

    /**
     * Get average optimisation
     *
     * @param int $visibility
     * @param int $search
     * @param int $content
     * @return int
     */
    private function getAverageOptimisation(int $visibility, int $search, int $content): int
    {
        return intval(($visibility + $search + $content) / 3);
    }
}
