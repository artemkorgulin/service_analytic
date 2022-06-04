<?php

namespace App\Helpers\Action;

use App\Contracts\ActionParams;
use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Models\Remote\Assistant\OzProduct;
use App\Models\Remote\Assistant\WbProduct;
use App\Models\Static\OzHistoryProduct;
use App\Models\Static\OzHistoryTop36;
use App\Models\Static\WbHistoryProduct;
use App\Models\Static\WbHistoryTop36;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

abstract class ActionHelper
{
    /**
     * @param Collection $products
     * @param int $vendorCode
     * @param string $date
     *
     * @return int|null
     */
    public function getProductSales(Collection $products, int $vendorCode, string $date): int|null
    {
        return $products->where('vendor_code', '=', $vendorCode)->where('date', '=',
            $date)->pluck('current_sales')->first();
    }

    /**
     * @param Collection $productsTop36
     * @param int $vendorCode
     * @param string $date
     *
     * @return int|null
     */
    public function getTop36Sales(Collection $productsTop36, int $vendorCode, string $date): int|null
    {
        return $productsTop36->where('vendor_code', '=', $vendorCode)->where('date', '=',
            $date)->pluck('current_sales')->first();
    }

    /**
     * @param Collection $products
     * @param int $vendorCode
     * @param string $field
     * @param string $date
     *
     * @return mixed
     */
    public function getFieldProduct(
        Collection $products,
        int $vendorCode,
        string $field,
        string $date
    ): mixed {
        return $products->where('vendor_code', '=', $vendorCode)->where('date', '=', $date)->pluck($field)->first();
    }

    /**
     * @param Collection $products
     * @param int $vendorCode
     * @param string $field
     * @param string $date
     *
     * @return mixed
     */
    public function getFieldTop36(Collection $products, int $vendorCode, string $field, string $date): mixed
    {
        return $products->where('vendor_code', '=', $vendorCode)->where('date', '=', $date)->pluck($field)->first();
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     *
     * @return array
     */
    public function getName(WbHistoryProduct|OzHistoryProduct $product): array
    {
        $name = [];
        $name['name'] = $product->name;
        $name['link'] = $product->url;
        $name['sku'] = $product->vendor_code;

        return $name;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @return array
     */
    public function getOptimization(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
    ): array {
        $optimization = [];
        $optimization['value'] = $product->optimization ?? 0;
        $optimization['trend'] = $optimization['value'] - ($productBeforeYesterday->optimization ?? 0);

        // к предыдущему дню произошло снижение на 10% и более
        $optimization['alert'] = $this->getSignalFlag($optimization['value'], $optimization['trend'],
            ActionParams::OPTIMIZATION_TRIGGER_THRESHOLD_PERCENTAGE);

        return $optimization;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
     * @return array
     */
    public function getRating(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
    ): array {
        $value = [];
        $value['user']['value'] = $product->rating;
        $value['user']['trend'] = $productBeforeYesterday ? ($value['user']['value'] - $productBeforeYesterday->rating) : 0;
        $value['top36']['value'] = $productTop36Yesterday ? $productTop36Yesterday->rating_avg : 0;
        $value['top36']['trend'] = $productTop36sBeforeYesterday ? ($value['top36']['value'] - $productTop36sBeforeYesterday->rating_avg) : 0;
        // снижение рейтинга товара пользователя  больше, чем на 3% к предыдущему дню
        $value['alert'] = $this->getSignalFlag($value['user']['value'], $value['user']['trend'],
            ActionParams::RATING_TRIGGER_THRESHOLD_PERCENTAGE);

        return $value;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
     * @return array
     */
    public function getFeedbacks(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
    ): array {
        $value = [];
        $value['user']['value'] = $product->comments;
        $value['user']['trend'] = $productBeforeYesterday ? ($value['user']['value'] - $productBeforeYesterday->comments) : 0;
        $value['top36']['value'] = $productTop36Yesterday ? $productTop36Yesterday->comments_avg : 0;
        $value['top36']['trend'] = $productTop36sBeforeYesterday ? ($value['top36']['value'] - $productTop36sBeforeYesterday->comments_avg) : 0;

        return $value;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @return array
     */
    public function getAvgPositionCategory(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday,
    ): array {
        $position = [];
        $position['user']['value'] = $product->position_category ?? 0;
        $position['user']['trend'] = $position['user']['value'] - ($productBeforeYesterday ? ($productBeforeYesterday->position_category ?? 0) : 0);

        // снижение средних позиций в поиске на 10% и более по сравнению с предыдущим днем
        $position['user']['alert'] = $this->getSignalFlag($position['user']['value'], $position['user']['trend'],
            ActionParams::POSITION_CATEGORY_TRIGGER_THRESHOLD_PERCENTAGE);

        return $position;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @return array
     */
    public function getAvgPositionSearch(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
    ): array {
        $position = [];
        $position['user']['value'] = $product->position_search ?? 0;
        $position['user']['trend'] = $position['user']['value'] - ($productBeforeYesterday ? ($productBeforeYesterday->position_search ?? 0) : 0);

        // снижение средних позиций в категории на 10% и более по сравнению с предыдущим днем
        $position['user']['alert'] = $this->getSignalFlag($position['user']['value'], $position['user']['trend'],
            ActionParams::POSITION_SEARCH_TRIGGER_THRESHOLD_PERCENTAGE);

        return $position;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday
     * @param WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
     * @return array
     */
    public function getImagesCount(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36Yesterday,
        WbHistoryTop36|OzHistoryTop36|null $productTop36sBeforeYesterday
    ): array {
        $value = [];
        $value['user']['value'] = $product->images;
        $value['user']['trend'] = $productBeforeYesterday ? ($value['user']['value'] - $productBeforeYesterday->images) : 0;
        $value['top36']['value'] = $productTop36Yesterday ? $productTop36Yesterday->images_avg : 0;
        $value['top36']['trend'] = $productTop36sBeforeYesterday ? ($value['top36']['value'] - $productTop36sBeforeYesterday->images_avg) : 0;

        // среднее количество изображений в топ 36 увеличилось на 2 и более
        $value['alert'] = $value['top36']['trend'] >= ActionParams::IMAGE_TRIGGER_THRESHOLD;

        return $value;
    }

    /**
     * @param WbHistoryProduct|OzHistoryProduct $product
     * @param WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
     * @return array
     */
    public function getAuthorshipProtection(
        WbHistoryProduct|OzHistoryProduct $product,
        WbHistoryProduct|OzHistoryProduct|null $productBeforeYesterday
    ): array
    {
        $escrow = [];
        $escrow['value'] = $product->escrow ?? 0;
        $escrow['trend'] = $escrow['value'] - ($productBeforeYesterday ? ($productBeforeYesterday->escrow ?? 0) : 0);

        return $escrow;
    }

    /**
     * @param int|null $value
     * @param int $trend
     * @param int $percent
     *
     * @return bool
     */
    public function getSignalFlag(?int $value, int $trend, int $percent): bool
    {
        if (($value > 0 && (abs(100 * $trend / $value) > $percent) && $trend < 0) || ($value === 0 && $trend < 0)) {
            return true;
        }

        return false;
    }
}