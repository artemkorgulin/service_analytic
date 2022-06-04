<?php

namespace App\Services\Wildberries;

use App\Classes\Helper;
use App\Models\NotShowingWbCharacteristics;
use App\Models\WbCategory;
use App\Models\WbFeature;
use App\Models\WbProduct;
use App\Models\YesNoWbCharacteristics;
use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Services\V2\OptimisationHistoryService;
use App\Services\Analytics\WbAnalyticsService;
use App\Helpers\WbCharacteristicsHelper;
use Illuminate\Support\Facades\Log;
use stdClass;

class WildberriesShowProductService implements WildberriesProductRepositoryInterface
{

    const maxItemChars = [
        'Ключевые слова' => 50,
        'Назначение' => 50,
        'Направление' => 50,
        'Комплектация' => 100,
    ];

    /**
     * Show one product
     *
     * @param integer $id
     * @param WbAnalyticsService $wbAnalyticsService
     * @return array
     */

    public function showProduct($id, $wbAnalyticsService)
    {
        $product = WbProduct::currentUserAndAccount()
            ->where('id', $id)->with('nomenclatures')
            ->first();
        if (!$product) {
            return ['status' => 'error', 'message' => 'Product not found!', 'code' => 404];
        }

        $product = $this->getProductFeatures($product);

        if (isset($product->rating)) {
            $product->rating = doubleval(number_format($product->rating, 1, '.', ''));
        }
        $product->optimization = $product->optimization ? intval($product->optimization) : 0;

        $product->searchOptimizationFields = $this->getOptimizationFields($product);

        $product->optimisationHistory = OptimisationHistoryService::productHistory($product);

        try {
            $productAnalytics = $wbAnalyticsService->getProductsRating([$product->id]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
        if (isset($productAnalytics['data'][0][$product->id])) {
            $product->rating = $productAnalytics['data'][0][$product->id];
        } else {
            $product->rating = 0;
        }

        $product->data = Helper::getUsableData($product);

        WbCharacteristicsHelper::removeCharacteristicTitle($product->data);

        return ['status' => 'success', 'data' => $product];
    }

    /**
     * Get recommended characteristics
     * @param $product
     * @return mixed
     */
    private function getRecommendedCharacteristics(&$product): mixed
    {
        $recommendedCharacteristics = $product->recommended_characteristics;
        $data = optional(Helper::getUsableData($product));
        if (!empty($recommendedCharacteristics) && !empty($data->addin)) {
            foreach ($data->addin as $characteristic) {
                if ($characteristic->type === 'Состав') {
                    $object = new stdClass();
                    $object->type = "Состав";
                    $object->maxCount = 10;
                    $object->required = false;
                    $object->dictionary = "/consists";
                    $object->isAvailable = true;
                    $object->useOnlyDictionaryValues = false;
                    $recommendedCharacteristics->addin[] = $object;
                    break;
                }
            }
        }
        if (!empty($recommendedCharacteristics->addin)) {
            $recommendedCharacteristics = WbCharacteristicsHelper::removeCharacteristicTitle($recommendedCharacteristics);
        }
        return $recommendedCharacteristics;
    }

    /**
     * Get product features
     * @param $product
     * @return mixed
     */
    public function getProductFeatures(&$product): mixed
    {
        $product->recommended_characteristics =
            optional(WbCategory::query()->firstWhere('name', $product->object))->data;
        $product->save();
        $product->recommended_characteristics = $this->getRecommendedCharacteristics($product);
        $product->recommended_characteristics = $this->getLimitedCharsCharacteristics($product);
        $product->required_characteristics = $this->getBooleanCharacteristics($product, WbFeature::EXCLUDE_WB_FEATURES);
        return $product;
    }

    /**
     * Get limited length of characteristics
     * @param $product
     * @return array
     */
    private function getLimitedCharsCharacteristics($product): array
    {
        if (optional($product->recommended_characteristics)->addin === null) {
            return [];
        }
        $characteristics = $product->recommended_characteristics;
        foreach (optional($characteristics)->addin as $item) {
            if (!in_array($item->type, WbFeature::EXCLUDE_WB_FEATURES)) {
                $item->isBoolean = (bool)YesNoWbCharacteristics::where('category', $product->object)
                    ->orWhere('category', '')
                    ->where('characteristic', $item->type)
                    ->count();
                if (isset(self::maxItemChars[$item->type])) {
                    $item->maxChar = self::maxItemChars[$item->type];
                }
                $characteristics->addin[] = $item;
            }
        }
        return $characteristics;
    }

    private function getBooleanCharacteristics($product, $excludeCharacteristics)
    {
        $requiredCharacteristics = [];
        foreach ($product->required_characteristics as $item) {
            if (!in_array($item->type, $excludeCharacteristics)) {
                $item->isBoolean = (bool)YesNoWbCharacteristics::where('category', $product->object)
                    ->orWhere('category', '')
                    ->where('characteristic', $item->type)
                    ->count();
                $item->useOnlyDictionaryValues = $item->useOnlyDictionaryValues || !NotShowingWbCharacteristics::where('category', $product->object)->where('characteristic', $item->type)->count();
                $requiredCharacteristics[] = $item;
            }
        }
        return $requiredCharacteristics;
    }

    private function getOptimizationFields($product)
    {
        //формируем массив полей для контентной оптимизации, которые надо показывать на фронте
        $searchOptimizationFields = [];
        $categoryCharacteristics = $product->recommended_characteristics->addin ?? [];
        foreach ($categoryCharacteristics as $categoryCharacteristic) {
            if ((str_contains(mb_strtolower($categoryCharacteristic->type), 'назначение') && $categoryCharacteristic->type != 'Назначение подарка')
                || $categoryCharacteristic->type == 'Направление') {
                $searchOptimizationFields[] = $categoryCharacteristic->type;
            }
        }
        return $searchOptimizationFields;
    }
}
