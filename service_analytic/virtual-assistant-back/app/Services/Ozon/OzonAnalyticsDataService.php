<?php

namespace App\Services\Ozon;

use App\Models\OzProduct;
use App\Models\OzProductAnalyticsData;
use App\Services\V2\OzonApi;
use DateInterval;
use DatePeriod;
use DateTime;

class OzonAnalyticsDataService
{
    /** Names of metrics by reports (maximum in report 14 metrics) */
    const METRICS = [
        //report 1
        [
            0 => 'hits_view_search',            //показы в поиске и в категории
            1 => 'hits_view_pdp',               //показы на карточке товара
            2 => 'hits_view',                   //всего показов
            3 => 'hits_tocart_search',          //в корзину из поиска или категории
            4 => 'hits_tocart_pdp',             //в корзину из карточки товара
            5 => 'hits_tocart',                 //всего добавлено в корзину
            6 => 'session_view_search',         //сессии с показом в поиске или в категории
            7 => 'session_view_pdp',            //сессии с показом на карточке товара
            8 => 'session_view',                //всего сессий
            9 => 'conv_tocart_search',          //конверсия в корзину из поиска или категории
            10 => 'conv_tocart_pdp',            //конверсия в корзину из карточки товара
            11 => 'conv_tocart',                //общая конверсия в корзину
            12 => 'revenue',                    //заказано на сумму
            13 => 'returns'                     //возвращено товаров
        ],

        //report 2
        [
            0 => 'cancellations',               //отменено товаров
            1 => 'ordered_units',               //заказано товаров
            2 => 'delivered_units',             //доставлено товаров
            3 => 'adv_view_pdp',                //показы на карточке товара, спонсорские товары
            4 => 'adv_view_search_category',    //показы в поиске и в категории, спонсорские товары
            5 => 'adv_view_all',                //показы всего, спонсорские товары
            6 => 'adv_sum_all',                 //всего расходов на рекламу
            7 => 'position_category',           //позиция в поиске и категории
            8 => 'postings',                    //отправления
            9 => 'postings_premium'             //отправления с подпиской Premium
        ]
    ];

    /** @var OzonApi */
    private OzonApi $client;

    /** @var bool */
    private bool $showInfo;

    /**
     * OzonAnalyticsDataService constructor.
     *
     * @param array $userAccount
     * @param bool $showInfo
     */
    public function __construct(array $userAccount, bool $showInfo = false)
    {
        $this->client = new OzonApi($userAccount['platform_client_id'], $userAccount['platform_api_key']);
        $this->showInfo = $showInfo;
    }

    /**
     * Update product metrics by period (dateFrom / dateTo)
     *
     * @param $product
     * @param $dateFrom
     * @param $dateTo
     * @return array
     * @throws \Exception
     */
    public function updateMetrics($product, $dateFrom, $dateTo): array
    {
        $result = [];
        $period = $this->getPeriod($dateFrom, $dateTo);
        if (iterator_count($period) > 50) {
            throw new \Exception('Не могу анализировать интервал более чем в 50 дней');
        }
        foreach ($period as $dt) {
            $report_date = $dt->format("Y-m-d");
            $reports = $this->getReports($product->sku_fbo, $report_date);
            if (!empty($reports)) {
                $metrics = $this->metricsByReports($reports);
            }
            if (!empty($metrics)) {
                $this->updateProductMetrics($product, $report_date, $metrics);
                $result[$report_date] = $metrics;
            }
        }
        return $result;
    }

    /**
     * Get array of metric names by reports
     *
     * @return array
     */
    public static function getMetricNamesByReports(): array
    {
        $metrics = [];
        foreach (self::METRICS as $report) {
            $reportNames = [];
            foreach ($report as $name) {
                $reportNames[] = $name;
            }
            $metrics[] = $reportNames;
        }
        return $metrics;
    }

    /**
     * Get period by days
     *
     * @param $dateFrom
     * @param $dateTo
     * @return DatePeriod
     * @throws \Exception
     */
    private function getPeriod($dateFrom, $dateTo): DatePeriod
    {
        $begin = new DateTime($dateFrom);
        $end = new DateTime($dateTo);
        $interval = DateInterval::createFromDateString('1 day');
        return new DatePeriod($begin, $interval, $end);
    }

    /**
     * Get reports for SKU by date
     *
     * @param $sku
     * @param $date
     * @return array
     */
    private function getReports($sku, $date): array
    {
        return $this->client->getAnalyticsData($date, $sku);
    }

    /**
     * Set and return result array of metrics by reports
     *
     * @param array $reports
     * @return array
     */
    private function metricsByReports(array $reports): array
    {
        $metrics = [];
        if (!empty($reports)) {
            foreach ($reports as $num => $report) {
                if (!empty($report['result']['data'])) {
                    foreach ($report['result']['data'] as $data) {
                        if (!empty($data['dimensions'][0]['name']) && empty($metrics['name'])) {
                            $metrics['name'] = $data['dimensions'][0]['name'];
                        }
                        foreach ($data['metrics'] as $metricNum => $metricVal) {
                            $metricVal = (int)round($metricVal);
                            $metricName = self::METRICS[$num][$metricNum];
                            $metrics[$metricName] = $metricVal;
                        }
                    }
                }
            }
        }
        return $metrics;
    }

    /**
     * Update or create metrics of product
     *
     * @param OzProduct $product
     * @param string $date
     * @param array $metrics
     */
    private function updateProductMetrics(OzProduct $product, string $date, array $metrics): void
    {
        $data = OzProductAnalyticsData::firstOrNew([
            'user_id' => $product->user_id,
            'account_id' => $product->account_id,
            'product_id' => $product->id,
            'external_id' => $product->external_id,
            'sku' => $product->sku_fbo,
            'report_date' => $date,
        ]);
        foreach ($metrics as $metricName => $metricVal) {
            $data->$metricName = $metricVal;
        }
        $data->save();
    }
}
