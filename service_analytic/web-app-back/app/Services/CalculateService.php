<?php

namespace App\Services;

use App\Models\Promocode;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BillingOrderRequest;
use App\Http\Requests\Api\BillingCheckPriceRequest;
use App\Models\OldTariff;
use App\Models\PromocodeUser;
use App\Models\User;

/**
 * Сервис рассчёта цены, в зависимости от периода бронирования
 */
class CalculateService
{
    public const DISCOUNT_1 = 0;
    public const DISCOUNT_3 = .10;
    public const DISCOUNT_6 = .20;
    public const DISCOUNT_12 = .30;

    /**
     * Возможные периоды бронирования
     *
     * (нужно для вывода в админке)
     */
    public $possiblePeriods;

    public function __construct()
    {
        $this->possiblePeriods =  [
            1 => __('order.period.1'),
            3 => __('order.period.3'),
            6 => __('order.period.6'),
            12 => __('order.period.12'),
        ];
    }

    /**
     * Узнать стоимость набора тарифов в зависимости от периода
     * оплаты и особенностей пользователя
     *
     * @param array                     $tariffs       массив номеров тарифов
     * @param int                       $period        период оплаты тарифов
     * @param \App\Models\PromocodeUser $promocodeUser закреплённый за пользователем промокод
     *
     * @return float
     */
    public static function calculatePriceTariffsByDiscount(array $tariffs, int $period, PromocodeUser $promocodeUser = null): float
    {
        $tariffs = is_array($tariffs) ? $tariffs : [$tariffs];

        $tariffsPrice = OldTariff::select(DB::raw('SUM(tariff_price) as sum_price'))
            ->whereIn('tariff_id', $tariffs)->first()->sum_price;

        $tariffsPrice = match ($period) {
            3 => ($tariffsPrice - ($tariffsPrice * self::DISCOUNT_3)) * 3,
            6 => ($tariffsPrice - ($tariffsPrice * self::DISCOUNT_6)) * 6,
            12 => ($tariffsPrice - ($tariffsPrice * self::DISCOUNT_12)) * 12,
            default => $tariffsPrice - ($tariffsPrice * self::DISCOUNT_1)
        };

        if ($promocodeUser) {
            $tariffsPrice = self::applyPromocodeDiscount($tariffsPrice, $promocodeUser);
        }

        return number_format((float)$tariffsPrice, 2, '.', '');
    }

    /**
     * Применить размер скидки из промокода
     */
    protected static function applyPromocodeDiscount($price, $promocodeUser)
    {
        return self::subtractPercent($price, $promocodeUser->promocode->discount);
    }

    /**
     * Получить массив данных по скидкам для данного тарифа
     *
     * @return array
     */
    public static function discountsForTariff($tariffId, $userPromocode)
    {
        $data = [
            'using_promocode' => false,
            'promocode_discount' => 0,
            'for_1_month' => self::calculatePriceTariffsByDiscount([$tariffId], 1, $userPromocode),
            'for_3_month' => self::calculatePriceTariffsByDiscount([$tariffId], 3, $userPromocode),
            'for_6_month' => self::calculatePriceTariffsByDiscount([$tariffId], 6, $userPromocode),
            'for_12_month' => self::calculatePriceTariffsByDiscount([$tariffId], 12, $userPromocode),
        ];

        if ($userPromocode) {
            $data['using_promocode'] = true;
            $data['promocode_discount'] = $userPromocode->promocode->discount;
        }

        return $data;
    }


    public static function subtractPercent($price, $percent)
    {
        return $price - ($price * ($percent / 100));
    }


    /**
     * Расчёт цен для запроса по новой механики тарифов для http запроса
     */
    public function priceCalculationForBillingOrderRequest(BillingCheckPriceRequest|BillingOrderRequest $request)
    {
        $duration = $request->duration;

        $services = $request->services;
        foreach ($services as &$service) {
            if ($service['alias'] === 'corp') {
                $service['amount'] = $duration;
            }
        }

        $promocode = null;
        if ($request->promocode) {
            $promocode = Promocode::where('code', $request->promocode)
                ->where(function ($q) use ($request) {
                    $q->where('usage_limit', -1)
                        ->orWhere('usage_limit', '>', 0);
                })
                ->first();
        }

        return $this->priceCalculation($duration, $services, $promocode);
    }

    /**
     * Расчёт цен для запроса по новой механики тарифов
     */
    public function priceCalculation($duration = 1, $services = [], $promocodeUser = null)
    {
        $data = [
            'begin_price' => 0,
            'begin_price_with_sku_discount' => 0,
            'begin_price_with_sku_period_discount' => 0,
            'begin_price_with_sku_period_promo_discount' => 0,
            'total_price' => 0,
            'price_per_period_for_services' => 0,
            'corp' => [],
            'services' => [],
        ];

        $skuDiscount = [
            'percentage' => 0,
            'value' => 0,
        ];

        if ($services) {
            foreach ($services as $service) {
                $calculatedAmount = $service['amount'];
                if ($service['alias'] === 'semantic') {
                    if ($service['amount'] < 100) {
                        $calculatedAmount = 100;
                    }
                }

                $serviceModel = \App\Models\Service::where('alias', $service['alias'])->first();
                $serviceTotalPrice = $serviceModel->humanTotalPriceFor($calculatedAmount);
                $totalPriceWithoutDiscounts = $serviceModel->humanTotalPriceWithoutDiscounts($calculatedAmount);
                $serviceDiscount = $serviceModel->getDiscount($calculatedAmount);
                $serviceDiscountPercent = $serviceDiscount * 100;
                $serviceDiscountMoney = $totalPriceWithoutDiscounts * $serviceDiscount;

                $dataService = [
                    "service_id" => $service['id'],
                    "amount" => $service['amount'],
                    "alias" => $service['alias'],
                    "price_per_item" => $serviceModel->humanPricePerItemFor($calculatedAmount),
                    "total_price_without_discounts" => $totalPriceWithoutDiscounts,
                    "total_price" => $serviceTotalPrice,
                    "discount" => [
                        'percentage' => $serviceDiscountPercent,
                        'value' => $serviceDiscountMoney,
                    ],
                ];
                $data["services"][] = $dataService;
                $data['price_per_period_for_services'] += $serviceTotalPrice;

                if ($service['alias'] === 'semantic') {
                    $data["begin_price"] = $dataService['total_price_without_discounts'] * $duration;
                    $data["begin_price_with_sku_discount"] = $serviceTotalPrice * $duration;

                    $skuDiscount['percentage'] = $serviceDiscountPercent;
                    $skuDiscount['value'] = $serviceDiscountMoney * $duration;
                }

                if ($service['alias'] === 'corp') {
                    $data['corp'] = $dataService;
                }
            }
        }

        $durationDiscount = $this->durationDiscount($duration);
        $durationDiscountPercent = $durationDiscount * 100;
        $durationDiscountMoney = $data["begin_price_with_sku_discount"] * $durationDiscount;
        $data['begin_price_with_sku_period_discount'] = $data["begin_price_with_sku_discount"] - $durationDiscountMoney;

        $promocodeDiscount = 0;
        $data['begin_price_with_sku_period_promo_discount'] = $data['begin_price_with_sku_period_discount'];
        $promocodeDiscountMoney = 0;
        if ($promocodeUser) {
            $promocodeDiscount = $promocodeUser->discount;
            $promocodeDiscountMoney = $data['begin_price_with_sku_period_discount'] * $promocodeDiscount / 100;
            $data['begin_price_with_sku_period_promo_discount'] = $data['begin_price_with_sku_period_discount'] - $promocodeDiscountMoney;
        }

        if ($data['corp']) {
            $data['total_price'] = $data['begin_price_with_sku_period_promo_discount'] + $data['corp']['total_price_without_discounts'];
        } else {
            $data['total_price'] = $data['begin_price_with_sku_period_promo_discount'];
        }

        // Значения в процентах
        $data['discounts'] = [
            "sku" =>  $skuDiscount,
            "duration" =>  [
                'percentage' => $durationDiscountPercent,
                'value' => $durationDiscountMoney,
            ],
            "promocode" => [
                'percentage' => $promocodeDiscount,
                'value' => $promocodeDiscountMoney,
            ],
        ];

        $data['begin_price'] = round($data['begin_price']);
        $data['begin_price_with_sku_discount'] = round($data['begin_price_with_sku_discount']);
        $data['begin_price_with_sku_period_discount'] = round($data['begin_price_with_sku_period_discount']);
        $data['begin_price_with_sku_period_promo_discount'] = round($data['begin_price_with_sku_period_promo_discount']);
        $data['total_price'] = round($data['total_price']);
        $data['price_per_period_for_services'] = round($data['price_per_period_for_services']);

        return $data;
    }

    /**
     * Посчитать сколько стоят услуги за весь период с учётом скидки
     */
    protected function calculatePriceForDuration($periodPrice, $duration)
    {
        return ($periodPrice - ($periodPrice * $this->durationDiscount($duration))) * $duration;
    }

    /**
     * Узнать размер скидки на услуги за данный период
     */
    protected function durationDiscount(int $duration)
    {
        return match ($duration) {
            3 => self::DISCOUNT_3,
            6 => self::DISCOUNT_6,
            12 => self::DISCOUNT_12,
            default => self::DISCOUNT_1,
        };
    }

    /**
     * Получить одну цифру, сколько человек должен будет заплатить за данный заказ
     */
    public function finalPriceForBillingOrderRequest(BillingOrderRequest $request)
    {
        $data = $this->priceCalculationForBillingOrderRequest($request);
        return $data['total_price'];
    }

    /**
     * Получить информацию о скидках при продолжительности бронировани
     */
    public function discountsInformation($user)
    {
        $data = [
            'using_promocode' => false,
            'promocode_discount' => 0,
            'months' => [
                ['period' => 1, 'discount' => 100 * self::DISCOUNT_1],
                ['period' => 3, 'discount' => 100 * self::DISCOUNT_3],
                ['period' => 6, 'discount' => 100 * self::DISCOUNT_6],
                ['period' => 12, 'discount' => 100 * self::DISCOUNT_12],
            ],
            'sku' => [
                ['sku' => 1, 'discount' => 0],
                ['sku' => 2500, 'discount' => 2500 * Service::PERCENT_SKU_DISCOUNT * 100],
                ['sku' => 5000, 'discount' => 5000 * Service::PERCENT_SKU_DISCOUNT * 100],
                ['sku' => 7500, 'discount' => 7500 * Service::PERCENT_SKU_DISCOUNT * 100],
                ['sku' => 10000, 'discount' => 10000 * Service::PERCENT_SKU_DISCOUNT * 100],
            ],
        ];

        if ($user->haveUnusedDiscountPromocode()) {
            $userPromocode = $user->nextDiscountPromocode();
            if ($userPromocode) {
                $data['using_promocode'] = true;
                $data['promocode_discount'] = $userPromocode->promocode->discount;
            }
        }

        return $data;
    }

}
