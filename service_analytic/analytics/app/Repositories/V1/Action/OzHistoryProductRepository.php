<?php

namespace App\Repositories\V1\Action;

use App\Contracts\ActionParams;
use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Helpers\StatisticQueries;
use App\Models\Ozon\ProductInfo;
use App\Models\Static\OzHistoryProduct;
use App\Models\Static\OzHistoryTop36;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Staudenmeir\LaravelCte\Query\Builder as StaudenmeirBuilder;
use stdClass;

class OzHistoryProductRepository implements HistoryRepositoryInterface
{
    private string $beforeYesterday;

    private OzHistoryProduct|null $beforeYesterdayProduct;

    private OzHistoryTop36|null $productTop36;

    private OzHistoryTop36|null $beforeYesterdayProductTop36;

    private string $yesterday;
    private string $startPeriodDate;
    private $productsTop36;
    private string $startWeeklyDate;

    public function __construct()
    {
        $this->beforeYesterday = Carbon::yesterday()->subDay()->format('Y-m-d');
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
        $this->startPeriodDate = Carbon::yesterday()->subDays(ActionParams::PERIOD_MONTH)->format('Y-m-d');
        $this->startWeeklyDate = Carbon::yesterday()->subDays(ActionParams::PERIOD)->format('Y-m-d');
    }

    /**
     * @param int $vendorCode
     * @return OzHistoryProduct|null
     */
    public function getProductByProductInfo(int $vendorCode, string $date): ProductInfo|null
    {
        return ProductInfo::query()->where('vendor_code', '=', $vendorCode)->where('date', '=', $date)->first();
    }

    /**
     * @param Collection $products
     * @param OzHistoryProduct $product
     * @return OzHistoryProduct|null
     */
    public function getProductBeforeDate(Collection $products, $product): OzHistoryProduct|null
    {
        if (!isset($this->beforeYesterdayProduct) || $this->beforeYesterdayProduct->vendor_code !== $product->vendor_code
        ) {
            $this->beforeYesterdayProduct = $products->where('vendor_code', '=', $product->vendor_code)
                ->where('date', '=', $this->beforeYesterday)->first();
        }

        return $this->beforeYesterdayProduct;
    }

    /**
     * @param OzHistoryProduct $product
     *
     * @return OzHistoryTop36|null
     */
    public function getProductTop36(
        Collection $productsTop36Yesterday,
        OzHistoryProduct $product
    ): OzHistoryTop36|null {
        if (!isset($this->productsTop36) || $product->vendor_code !== $this->productTop36->first()->vendor_code) {
            $this->productTop36 = $productsTop36Yesterday
                ->where('vendor_code', '=', $product->vendor_code)
                ->where('date', '=', $this->yesterday)->first();
        }

        return $this->productTop36;
    }

    /**
     * @param Collection $productTop36sBeforeYesterday
     * @param OzHistoryProduct $product
     *
     * @return OzHistoryTop36|null
     */
    public function getProductTop36BeforeDate(
        Collection $productTop36sBeforeYesterday,
        OzHistoryProduct $product
    ): OzHistoryTop36|null {
        if (!isset($this->beforeYesterdayProductTop36) || $product->vendor_code !== $this->productTop36->vendor_code) {
            $this->beforeYesterdayProductTop36 = $productTop36sBeforeYesterday
                ->where('vendor_code', '=', $product->vendor_code)
                ->where('date', '=', $this->beforeYesterday)->first();
        }

        return $this->beforeYesterdayProductTop36;
    }

    /**
     * @return LazyCollection
     */
    public function getProductList(): Collection|LengthAwarePaginator
    {
        $account = UserService::getAccountActive();
        $userId = UserService::getUserId();

        return OzHistoryProduct::query()
            ->where('account_id', '=', $account['pivot']['account_id'])
            ->where('user_id', '=', $userId)
            ->whereBetween('date', [$this->startWeeklyDate, $this->yesterday])
            ->get();
    }

    /**
     * @return Collection|LengthAwarePaginator
     */
    public function getProductListTop36($vendorCodes): Collection|LengthAwarePaginator
    {
        return OzHistoryTop36::query()
            ->whereBetween('date', [$this->startWeeklyDate, $this->yesterday])
            ->whereIn('vendor_code', $vendorCodes)
            ->get();
    }

    /**
     * @return Collection
     */
    public function getOzAccountList(): Collection
    {
        return OzHistoryProduct::query()
            ->where('date', '=', $this->yesterday)
            ->groupBy('account_id')
            ->pluck('account_id');
    }

    /**
     * @param int $accountId
     *
     * @return Collection|null
     */
    public function getProductsForOzAccount(int $accountId): ?Collection
    {
        return OzHistoryProduct::query()->where('account_id', '=', $accountId)
            ->where('date', '=', $this->yesterday)->get();
    }

    /**
     * @param int $vendorCode
     * @param int $countDays
     * @return \Staudenmeir\LaravelCte\Query\Builder
     */
    public function getHistoryTop36(int $vendorCode, int $countDays = 1): StaudenmeirBuilder
    {
        $endDate = Carbon::now()->subDays($countDays)->format('Y-m-d');
        $startDate = Carbon::now()->subDays($countDays + 6)->format('Y-m-d');

        $lastPositionsTop36 = DB::connection('ozon')->table('positions as p')->selectRaw('
                        p.web_id, p.vendor_code,
                        rank() over (partition by "p"."web_id", "p"."position" order by p.date desc)
                    ')->whereBetween('p.date', [
            $startDate,
            $endDate,
        ])->where('p.position', '<', '37');

        return DB::connection('ozon')->query()->fromSub($lastPositionsTop36, 'positions')->selectRaw('
                positions.web_id,
                count(positions.vendor_code) as product_quantity,
                min(method_oz_product_info.images_count) filter (where method_oz_product_info.images_count > 0) as photo_min,
                min(method_oz_product_info.comments_count) filter (where method_oz_product_info.comments_count > 0) as comments_min,
                round((avg(method_oz_product_info.final_price))/100, 2) as price_avg,
                min(method_oz_product_info.grade) filter (where method_oz_product_info.grade > 0) as rating_min,
                round((avg(method_oz_product_info.grade) filter (where method_oz_product_info.grade > 0)), 2) as rating_avg
            ')->leftJoin(DB::connection('ozon')->raw(StatisticQueries::getOzProductInfo('positions', $startDate,
            $endDate)), 'positions.vendor_code', '=', 'method_oz_product_info.vendor_code')->where('rank',
            1)->groupBy('positions.web_id');
    }

    /**
     * @param int $vendorCode
     * @return \Staudenmeir\LaravelCte\Query\Builder
     */
    public function getRecommendationsForTop36(int $vendorCode): StaudenmeirBuilder
    {
        $recommendation = $this->getHistoryTop36($vendorCode);

        return DB::connection('ozon')->query()->fromSub($recommendation, 'r')->selectRaw('
                web_id,
                max(r.photo_min) as photo,
                max(r.comments_min) as comments,
                max(r.price_avg) as price,
                max(r.rating_min) as rating,
                max(r.rating_avg) as rating_avg
            ')->groupBy('r.web_id');
    }

    /**
     * @param int $vendorCode
     * @return stdClass|null
     */
    public function getProductByVendorCode(int $vendorCode, string $date): stdClass|null
    {
        return DB::connection('ozon')->query()->selectRaw('cp.name, p.web_id, p.position')->from('card_products as cp')->join('positions as p',
            'cp.vendor_code', '=', 'p.vendor_code')->where('cp.vendor_code', '=', $vendorCode)->where('p.date', '=',
            $date)->first();
    }

    /**
     * @param OzHistoryProduct $product
     * @return array
     * @throws \Exception
     */
    public function getAuthorshipProtection(OzHistoryProduct $product): array
    {
        $escrow = [];
        $escrow['value'] = $product->escrow ?? 0;
        $escrow['trend'] = $escrow['value'] - ($this->getProductBeforeDate($product)->escrow ?? 0);

        return $escrow;
    }

    public function getProducts(int $vendorCode): Collection|array
    {
        return OzHistoryProduct::query()->where('vendor_code', '=', $vendorCode)
            ->WhereBetween('date', [$this->startPeriodDate, $this->yesterday])->get();
    }

    /**
     * @param OzHistoryProduct $product
     * @param OzHistoryProduct $productBeforeYesterday
     * @param Collection $productsPeriodDays
     * @param Collection $productsTop36PeriodDays
     * @return array
     */
    public function getDiagramSales(
        OzHistoryProduct $product,
        OzHistoryProduct $productBeforeYesterday,
        Collection $productsPeriodDays,
        Collection $productsTop36PeriodDays
    ): array {
        $sales = [];
        $salesUser = $productsPeriodDays
            ->where('vendor_code', '=', $product->vendor_code)
            ->pluck('current_sales', 'date')->toArray();
        $salesTop36 = $productsTop36PeriodDays
            ->where('vendor_code', '=', $product->vendor_code)
            // ->groupBy('date')
            ->pluck('sale_avg', 'date')->toArray();

        $period = ActionParams::PERIOD;
        while ($period != 0) {
            $period --;
            $date = Carbon::now()->subDays($period)->format('Y-m-d');

            if(array_key_exists($date, $salesUser )){
                $sales['user'][] = $salesUser[$date];
            }else{
                $sales['user'][] = 0;
            }

            if(array_key_exists($date, $salesTop36)){
                $sales['top36'][] = $salesTop36[$date];
            }else{
                $sales['top36'][] = 0;
            }
        }

        $sales['value'] = $product->current_sales ?? 0;
        $sales['trend'] = $sales['value']
            - $productBeforeYesterday->current_sales ?? 0;
        // ?????????????? ?????????????????? ??????????, ?????? ???? 10% ?? ?????????????? ????????????(7 ????????)
        $sales['alert'] = false;  // ????????, ?????????????????????????????? ?? ????????????????

        return $sales;
    }

    /**
     * @param array $data
     * @param string $date
     * @return array|null
     */
    public function getTroubles(array $data, string $date): array|null
    {
        if (!$key = array_search($date, $data['dates'])) {
            return [];
        }

        $result = $this->getTroublesOptimization($data, $key);
        $result = array_merge($result, $this->getTroublesPositionCategory($data, $key));
        $result = array_merge($result, $this->getTroublesPositionSearch($data, $key));
        $result = array_merge($result, $this->getTroublesSales($data, $key));

        return array_merge($result, $this->getTroublesRating($data, $key));
    }

    /**
     * @param array $data
     * @return array
     */
    public function getTroublesSummary(array $data): array
    {
        $product = OzHistoryProduct::query()->where('vendor_code', $data['id'])->first();

        if (!$key = array_search($this->yesterday, $data['dates']) || $product === null) {
            return [];
        }

        $troubles = [];
        $troubles['troubles'] = [];
        if ($this->getSignalFlag($data['optimization'][$key],
            $data['optimization'][$key] - $data['optimization'][$key - 1],
            ActionParams::OPTIMIZATION_TRIGGER_THRESHOLD_PERCENTAGE)) {
            $troubles['troubles'][] = ActionParams::OPTIMIZATION_LONG;
        }

        if ($this->getSignalFlag($data['rating_user'][$key],
            $data['rating_user'][$key] - $data['rating_user'][$key - 1],
            ActionParams::RATING_TRIGGER_THRESHOLD_PERCENTAGE)) {
            $troubles['troubles'][] = ActionParams::POSITION_RATING_LONG;
        }

        if ($this->getSignalFlag($data['avg_position_search_user'][$key],
            $data['avg_position_search_user'][$key] - $data['avg_position_search_user'][$key - 1],
            ActionParams::POSITION_SEARCH_TRIGGER_THRESHOLD_PERCENTAGE)) {
            $troubles['troubles'][] = ActionParams::POSITION_SEARCH_LONG;
        }

        if ($this->getSignalFlag($data['avg_position_category_user'][$key],
            $data['avg_position_category_user'][$key] - $data['avg_position_category_user'][$key - 1],
            ActionParams::POSITION_CATEGORY_TRIGGER_THRESHOLD_PERCENTAGE)) {
            $troubles['troubles'][] = ActionParams::POSITION_CATEGORY_LONG;
        }

        //TODO ?????????? ???????????? ???????????? ?????????? ???????? ?????? ???? ???????????????????? 7 ????????
        if ($this->getSignalFlag($data['sales_user'][$key], $data['sales_user'][$key] - $data['sales_user'][$key - 1],
            ActionParams::SAlES_TRIGGER_THRESHOLD_PERCENTAGE)) {
            $troubles['troubles'][] = ActionParams::POSITION_SALES_LONG;
        }

        return array_merge($troubles, [
            'name' => $product->name,
            'link' => $product->url,
            'date' => $this->yesterday,
        ]);
    }

    /**
     * @param int|null $value
     * @param int $trend
     * @param int $percent
     * @return bool
     */
    public function getSignalFlag(?int $value, int $trend, int $percent): bool
    {
        if (($value > 0 && (abs(100 * $trend / $value) > $percent) && $trend < 0)
            || ($value === 0 && $trend < 0)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param string $key
     * @return null[]|null
     */
    public function getTroublesOptimization(array $data, string $key): array|null
    {
        if ($this->getSignalFlag($data['optimization'][$key],
            $data['optimization'][$key] - $data['optimization'][$key - 1],
            ActionParams::OPTIMIZATION_TRIGGER_THRESHOLD_PERCENTAGE)) {
            return [
                [
                    'value' => $data['optimization'][$key],
                    'text' => ActionParams::OPTIMIZATION_SHORT,
                ],
                [
                    'value' => $data['optimization'][$key - 1],
                    'text' => ActionParams::OPTIMIZATION_SHORT_BEFORE,
                ],
            ];
        } else {
            return [];
        }
    }

    public function getTroublesRating(array $data, string $key): array|null
    {
        if ($this->getSignalFlag($data['rating_user'][$key],
            $data['rating_user'][$key] - $data['rating_user'][$key - 1],
            ActionParams::RATING_TRIGGER_THRESHOLD_PERCENTAGE)) {
            return [
                [
                    'value' => $data['rating_user'][$key],
                    'text' => ActionParams::POSITION_RATING_SHORT,
                    "key" => 'rating_user',
                ],
                [
                    'value' => $data['rating_user'][$key - 1],
                    'text' => ActionParams::POSITION_RATING_SHORT_BEFORE,
                    "key" => 'rating_user',
                ],
            ];
        } else {
            return [];
        }
    }

    public function getTroublesPositionSearch(array $data, string $key): array
    {
        if ($this->getSignalFlag($data['avg_position_search_user'][$key],
            $data['avg_position_search_user'][$key] - $data['avg_position_search_user'][$key - 1],
            ActionParams::POSITION_SEARCH_TRIGGER_THRESHOLD_PERCENTAGE)) {
            return [
                [
                    'value' => $data['avg_position_search_user'][$key],
                    'text' => ActionParams::POSITION_SEARCH_SHORT,
                    "key" => 'avg_position_search_user',
                ],
                [
                    'value' => $data['avg_position_search_user'][$key - 1],
                    'text' => ActionParams::POSITION_SEARCH_SHORT_BEFORE,
                    "key" => 'avg_position_search_user',
                ],
            ];
        } else {
            return [];
        }
    }

    /**
     * @param array $data
     * @param string $key
     * @return array
     */
    public function getTroublesPositionCategory(array $data, string $key): array
    {
        if ($this->getSignalFlag($data['avg_position_category_user'][$key],
            $data['avg_position_category_user'][$key] - $data['avg_position_category_user'][$key - 1],
            ActionParams::POSITION_CATEGORY_TRIGGER_THRESHOLD_PERCENTAGE)) {
            return [
                [
                    'value' => $data['avg_position_category_user'][$key],
                    'text' => ActionParams::POSITION_CATEGORY_SHORT,
                    "key" => 'avg_position_category_user',
                ],
                [
                    'value' => $data['avg_position_category_user'][$key - 1],
                    'text' => ActionParams::POSITION_CATEGORY_SHORT_BEFORE,
                    "key" => 'avg_position_category_user',
                ],
            ];
        } else {
            return [];
        }
    }

    /**
     * @param array $data
     * @param string $key
     * @return array
     */
    public function getTroublesSales(array $data, string $key): array
    {
        $sumSales = 0;
        $count = 0;
        for ($i = 1; $i <= 7; $i++) {
            if (isset($data['sales_user'][$key - $i])) {
                $sumSales += $data['sales_user'][$key - $i];
                $count++;
            }
        }

        $avgSalesBefore = $count > 0 ? round($sumSales / $count) : 0;
        if ($this->getSignalFlag($data['sales_user'][$key], $data['sales_user'][$key] - $avgSalesBefore,
            ActionParams::SAlES_TRIGGER_THRESHOLD_PERCENTAGE)) {
            return [
                [
                    'value' => $data['sales_user'][$key],
                    'text' => ActionParams::POSITION_SAlES_SHORT,
                    "key" => 'sales_user',
                ],
                [
                    'value' => $avgSalesBefore,
                    'text' => ActionParams::POSITION_SALES_SHORT_BEFORE,
                    "key" => 'sales_user',
                ],
            ];
        } else {
            return [];
        }
    }
}
