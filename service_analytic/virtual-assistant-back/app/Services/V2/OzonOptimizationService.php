<?php

namespace App\Services\V2;

use App\Models\OzProduct;
use App\Models\OzProductFeature;
use AnalyticPlatform\LaravelHelpers\Exceptions\EntityNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class OzonOptimizationService
{
    /**
     * Подсчёт степени поисковой оптимизации карточки
     * @return int
     */
    public static function calculateSearchOptimization($product): int
    {
        self::checkProductExist($product);
        $fields = [];
        $fields[] = $product->name;
        foreach ($product->descriptions as $description) {
            $fields[] = $description;
        }
        $counter = 0;
        $popularity = 0;
        $allPopularity = 0;
        $keywords = $product->platformSemantic()->get()->toArray();
        foreach ($keywords as $keyword) {
            if (isset($keyword['popularity'])) {
                $allPopularity += $keyword['popularity'];
            } else {
                $allPopularity++;
            }
        }
        foreach ($fields as $field) {
            foreach ($keywords as $key => $keyword) {
                if (strripos($field, $keyword['key_request']) !== false) {
                    if (isset($keyword['popularity'])) {
                        $popularity += $keyword['popularity'];
                    } else {
                        $popularity++;
                    }
                    $counter++;
                    unset($keywords[$key]);
                }
            }
        }

        if (!$allPopularity) return 0;
        $percent = intval(($popularity / $allPopularity) * 100);
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    /**
     * Подсчёт степени контентной оптимизации карточки
     * @return int
     */
    public static function calculateContentOptimization($product): int
    {
        self::checkProductExist($product);
        $allFeatures = DB::table('oz_category_to_feature')->where('category_id', $product->category_id)->count();
        $filledFeatures = OzProductFeature::where('product_id', $product->id)
            ->distinct('feature_id')
            ->count();
        if (!$allFeatures) return 0;
        $percent = (int)(($filledFeatures / $allFeatures) * 100);
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    /**
     * Подсчёт степени видимости товара
     * @return int
     */
    public static function calculateVisibilityOptimization($product): int
    {
        self::checkProductExist($product);
        $characteristics = optional($product->category)->characteristics;
        $popularitySum = 0;
        $selectedPopularitySum = 0;
        foreach ($characteristics as $items) {
            if (isset($items['options'])) {
                foreach ($items['options'] as $option) {
                    if (isset($option['popularity'])) {
                        $popularitySum += $option['popularity'];
                    }
                    if (!$items['is_collection']) break;
                }
            }
        }
        foreach ($product->characteristics as $characteristic) {
            if (isset($characteristic['selected_options'])) {
                foreach ($characteristic['selected_options'] as $selected) {
                    $selectedPopularitySum += $selected['popularity'];
                }
            }
        }
        if (!$popularitySum) return 0;
        $percent = intval(($selectedPopularitySum / $popularitySum) * 100);
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }


    /*
     * Проверка на существование товара
     */
    private static function checkProductExist($product)
    {
        if (is_int($product)) {
            $model = OzProduct::find($product);
        } elseif ($product instanceof OzProduct) {
            $model = $product;
        } else {
            throw new Exception('Wrong argument type');
        }

        if ($model === null) {
            throw new EntityNotFoundException('Товар не найден');
        }
    }
}
