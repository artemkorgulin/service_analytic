<?php

namespace App\Helpers;

use App\Models\Analytica\UserParams;
use App\Repositories\V1\Assistant\WbProductRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QueryBuilderHelper
{
    // TODO: оставить в классе только методы для создания запросов, остальное разнести в другие классы (часть вынести в библиотеку AnalyticPlatform)

    /**
     * Получить данные для графика продаж товара.
     *
     * @param  array  $vedorCodes
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    public static function getProductSales(array $vedorCodes, string $startDate, string $endDate): array
    {
        $datesPeriod = static::getDatesPeriod($startDate, $endDate);

        $sales = DB::table('product_info')
            ->select(DB::raw('vendor_code, date, MAX(current_sales) AS sale'))
            ->whereIn('vendor_code', $vedorCodes)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy(DB::raw('vendor_code, date'))
            ->get();

        $result = [];
        foreach ($sales as $sale) {
            if (!isset($result[$sale->vendor_code])) {
                $result[$sale->vendor_code] = $datesPeriod;
            }

            $result[$sale->vendor_code][$sale->date] = $sale->sale;
        }

        return $result;
    }

    /**
     * Получить данные для графика продаж продавца.
     *
     * @param array $supplierIds
     * @param int $brandId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getSupplierSales(array $supplierIds, int $brandId, string $startDate, string $endDate): array
    {
        $datesPeriod = static::getDatesPeriod($startDate, $endDate);

        $sales = DB::table('card_products')
            ->select(DB::raw('card_products.suppliers_id, product_info.date, SUM(product_info.current_sales) AS sale'))
            ->join('product_info', function ($join) use ($supplierIds, $brandId, $startDate, $endDate) {
                $join->on('card_products.vendor_code', '=', 'product_info.vendor_code')
                    ->where('card_products.brand_id', $brandId)
                    ->whereRaw('product_info.current_sales > 0')
                    ->whereIn('card_products.suppliers_id', $supplierIds)
                    ->whereBetween('product_info.date', [$startDate, $endDate]);
            })
            ->groupBy(DB::raw('card_products.suppliers_id, product_info.date'))
            ->get();

        $result = [];
        foreach ($sales as $sale) {
            if (!isset($result[$sale->suppliers_id])) {
                $result[$sale->suppliers_id] = $datesPeriod;
            }

            if (isset($result[$sale->suppliers_id][$sale->date])) {
                $sum = $result[$sale->suppliers_id][$sale->date];
            } else {
                $sum = 0;
            }

            $result[$sale->suppliers_id][$sale->date] = $sum + (int) $sale->sale;
        }

        return $result;
    }

    /**
     * Получить массив дней за период.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     * @throws \Exception
     */
    public static function getDatesPeriod(string $startDate, string $endDate): array
    {
        $dates = [];

        $period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime($endDate));
        foreach ($period as $date) {
            $dateFormat = $date->format("Y-m-d");
            $dates[$dateFormat] = 0;
        }

        return $dates;
    }

    /**
     * Получить wb продукты пользователя.
     *
     * @param  int  $userId
     * @return Collection
     */
    public static function getUserProducts(int $userId): Collection
    {
        return (new WbProductRepository())->getUserProductNmids($userId);
    }

    /**
     * @param  Request  $request
     * @param  array  $paramNames
     * @return bool
     */
    public static function saveUserParams(Request $request, array $paramNames)
    {
        $userId = $request->input('user')['id'];
        $url = $request->path();

        $params = [];

        foreach ($paramNames as $paramName) {
            if ($request->input($paramName)) {
                $params[$paramName] = $request->input($paramName);
            }
        }

        UserParams::updateOrCreate(
            [
                'user_id' => $userId,
                'url' => $url,
            ],
            [
                'params' => json_encode($params),
            ]
        );

        return true;
    }

    /**
     * @param  Request  $request
     * @return string
     */
    public static function paginationForQuery(Request $request): string
    {
        $currentPage = $request->page ?? 1;
        $perPage = $request->per_page ?? 50;
        $all = $request->show_all ?? false;

        if ($all) {
            return '';
        }

        $offset = ($currentPage - 1) * $perPage;

        return "limit $perPage offset $offset";
    }

    /**
     * @param  Request  $request
     * @param  string  $startString
     * @return string
     */
    public static function sortForQuery(Request $request, string $startString):string
    {
        if ($request->input('sort')) {
            $column = $request->input('sort')['col'];
            $sort = $request->input('sort')['sort'];

            return $startString . "$column $sort";
        }

        return '';
    }

    /**
     * Сгенерировать ссылку на изображение для товара wb.
     *
     * @param  string  $sku
     * @return string
     */
    public static function generateWbImageUrl(string $sku): string
    {
        //если длинна sku 7 берем первые 3 символа, если 8, то 4
        $part = substr($sku, 0, strlen($sku) - 4).'0000';

        return "https://images.wbstatic.net/big/new/$part/$sku-1.jpg";
    }

    /**
     * Формирует запрос типа multiIf для сегментации по цене
     *
     * @param  int  $segmentCount
     * @param  int  $minPrice
     * @param  int  $maxPrice
     * @param  string  $alias
     * @param  string  $columnName
     * @return string
     */
    public static function selectPriceRange(
        int $segmentCount,
        int $minPrice,
        int $maxPrice,
        string $alias = 'range',
        string $columnName = 'last_price'
    ): string {
        $step = ($maxPrice - $minPrice) <= 0 ? 1 : ceil(($maxPrice - $minPrice) / $segmentCount);
        $segments = 'multiIf(';
        while ($minPrice <= $maxPrice) {
            $segments .= sprintf('%1$s >= %2$s and %1$s <= %3$s, \'%2$s-%3$s\',',
                $columnName, $segments === 'multiIf(' ? $minPrice : $minPrice + 1, min($minPrice + $step, $maxPrice));
            $minPrice += $step;
        }
        $segments .= sprintf(" null) as %s", $alias);

        return $segments;
    }
}
