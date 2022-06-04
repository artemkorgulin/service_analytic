<?php

namespace App\Services;

use App\Contracts\StatisticInterface;
use Carbon\Carbon;

class CalculateService
{
    /**
     * Добавить расчетные показатели (ключевого слова)
     *
     * @param StatisticInterface $statistic
     */
    public static function calcCharacteristics(&$statistic)
    {
        $statistic->avg_1000_shows_price = $statistic->shows != 0
            ? 1000 * $statistic->cost / $statistic->shows
            : 0;

        $statistic->avg_click_price = $statistic->clicks != 0
            ? $statistic->cost / $statistic->clicks
            : 0;
    }

    /**
     * Пересчитать расчетные показатели (для товаров и РК)
     *
     * @param StatisticInterface $statistic
     */
    public static function recalcCharacteristics(&$statistic)
    {
        $statistic->ctr = $statistic->shows != 0
            ? 100 * $statistic->clicks / $statistic->shows
            : 0;

        self::calcCharacteristics($statistic);
    }

    /**
     * Расчитать показатели эффективности
     *
     * @param StatisticInterface $statistic
     */
    public static function calcCPOandACOS(&$statistic)
    {
        $statistic->cpo = $statistic->orders != 0
            ? $statistic->cost / $statistic->orders
            : 0;

        $statistic->acos = $statistic->profit != 0
            ? $statistic->cost / $statistic->profit
            : 0;
    }

    /**
     * Расчитать показатели эффективности
     *
     * @param StatisticInterface $statistic
     */
    public static function calcDRR(&$statistic)
    {
        $statistic->drr = $statistic->profit != 0
            ? ($statistic->cost / $statistic->profit)*100
            : 0;
    }

    /**
     * Расчитать показатели эффективности на основании популярности
     *
     * @param StatisticInterface $statistic
     */
    public static function calcPopularityCharacteristics(&$statistic)
    {
        $statistic->purchased_shows = $statistic->popularity != 0
            ? 100 * $statistic->shows / $statistic->popularity
            : 0;
    }

    /**
     * Расчитать показатели эффективности на основании популярности
     *
     * @param StatisticInterface $statistic
     */
    public static function recalcAllCharacteristics(&$statistic)
    {
        CalculateService::recalcCharacteristics($statistic);
        CalculateService::calcCPOandACOS($statistic);
        CalculateService::calcPopularityCharacteristics($statistic);
        CalculateService::calcDRR($statistic);
    }

    /**
     * Расчитать показатели эффективности на основании популярности
     *
     * @param StatisticInterface $statistic
     */
    public static function recalcAllCharacteristicsForStrategyHistory(&$statistic)
    {
        CalculateService::recalcAllCharacteristics($statistic);

        $statistic->avg_purchased_shows = $statistic->avg_popularity != 0
            ? 100 * $statistic->avg_shows / $statistic->avg_popularity
            : null;
    }

    /**
     * Преобразовать значения для вывода на фронт для аналитики
     *
     * @param StatisticInterface $statistic
     * @throws \Exception
     */
    public static function reassignColumns(&$statistic)
    {
        $date = new Carbon($statistic->date);
        $now  = Carbon::now();
        $checkDate = $date->diffInSeconds($now) < 2 || $date->diff($now)->days > 2;
        $purchasedShows = $statistic->purchased_shows ?? 0;

        $statistic->cost                 = number_format($statistic->cost                 ?? 0,2, ',', ' ');
        $statistic->clicks               = number_format($statistic->clicks               ?? 0,0, ',', ' ');
        $statistic->ctr                  = number_format($statistic->ctr                  ?? 0,2, ',', ' ');
        $statistic->avg_1000_shows_price = number_format($statistic->avg_1000_shows_price ?? 0,2, ',', ' ');
        $statistic->avg_click_price      = number_format($statistic->avg_click_price      ?? 0,2, ',', ' ');
        $statistic->orders               = number_format($statistic->orders               ?? 0,0, ',', ' ');
        $statistic->profit               = number_format($statistic->profit               ?? 0,2, ',', ' ');
        $statistic->cpo                  = number_format($statistic->cpo                  ?? 0,2, ',', ' ');
        $statistic->acos                 = number_format($statistic->acos                 ?? 0,2, ',', ' ');
        $statistic->drr                  = number_format($statistic->drr                  ?? 0,2, ',', ' ');

        if ($statistic->campaign_start_date && $statistic->campaign_end_date) {
            if ($statistic->campaign_start_date !== '0000-00-00' && $statistic->campaign_end_date !== '0000-00-00') {
                $startDate = new Carbon($statistic->campaign_start_date);
                $endDate   = new Carbon($statistic->campaign_end_date);

                $statistic->campaign_start_date = $startDate->format('d.m.Y');
                $statistic->campaign_end_date   = $endDate->format('d.m.Y');
            }else{
                $statistic->campaign_start_date = null;
                $statistic->campaign_end_date   = null;
            }
        }

        $statistic->purchased_shows = !$checkDate && ($statistic->popularity === null || $statistic->shows === null)
            ? __('front.popularity_not_set')
            : number_format($purchasedShows, $purchasedShows ? 2 : 0, ',', ' ');

        $statistic->popularity = !$checkDate && $statistic->popularity === null
            ? __('front.popularity_not_set')
            : number_format($statistic->popularity ?? 0,0, ',', ' ');

        $statistic->shows = !$checkDate && $statistic->shows === null
            ? __('front.popularity_not_set')
            : number_format($statistic->shows ?? 0,0, ',', ' ');
    }



    /**
     *  Преобразовать значения для вывода на фронт для кампаний
     *
     * @param StatisticInterface $statistic
     */
    public static function reassignCampaignColumns(&$statistic)
    {
        if( $statistic->daily_budget == 0 ) $statistic->daily_budget = __('front.budget_not_set');
        self::reassignColumns($statistic);
    }

    /**
     * Преобразовать значения для вывода на фронт для стратегий
     *
     * @param StatisticInterface $statistic
     */
    public static function reassignStrategyColumns(&$statistic)
    {
        $statistic->threshold           = number_format(100 * $statistic->threshold     ?? 0,2, ',', ' ');
        $statistic->avg_popularity      = number_format($statistic->avg_popularity      ?? 0,2, ',', ' ');
        $statistic->avg_shows           = number_format($statistic->avg_shows           ?? 0,2, ',', ' ');
        $statistic->avg_purchased_shows = number_format($statistic->avg_purchased_shows ?? 0,2, ',', ' ');

        self::reassignColumns($statistic);
    }

    /**
     * Преобразовать значения для вывода на фронт данных стратегий CPO
     *
     * @param StatisticInterface $statistic
     */
    public static function reassignStrategyCpoValues(&$statistic)
    {
        $statistic->shows                = number_format($statistic->shows                ?? 0,0, ',', ' ');
        $statistic->orders               = number_format($statistic->orders               ?? 0,0, ',', ' ');
        $statistic->clicks               = number_format($statistic->clicks               ?? 0,0, ',', ' ');
        $statistic->cost                 = number_format($statistic->cost                 ?? 0,2, ',', ' ');
        $statistic->profit               = number_format($statistic->profit               ?? 0,2, ',', ' ');
        $statistic->kvcr                 = number_format($statistic->kvcr                 ?? 0,2, ',', ' ');
        $statistic->ctr                  = number_format($statistic->ctr                  ?? 0,2, ',', ' ');
        $statistic->avg_click_price      = number_format($statistic->avg_click_price      ?? 0,2, ',', ' ');
        $statistic->avg_1000_shows_price = number_format($statistic->avg_1000_shows_price ?? 0,2, ',', ' ');
        $statistic->fcpo                 = number_format($statistic->fcpo                 ?? 0,2, ',', ' ');
        $statistic->acos                 = number_format($statistic->acos                 ?? 0,2, ',', ' ');

        if (isset($statistic->sku)) {
            $statistic->sku_url = config('ozon.detail_sku_url') .'/'. $statistic->sku;
        }
    }
}
