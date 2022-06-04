<?php

namespace App\Repositories\V2\Categories;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\QueryConditions\V2\QueryConditionCategoryProduct;
use App\Helpers\WildberriesHelper;
use App\Models\Clickhouse\CardProduct;
use App\Services\GraphForProductService;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\ArrayShape;
use Tinderbox\Clickhouse\Exceptions\ClientException;

class CategoryStatisticRepository
{
    protected string $category;
    protected mixed $startDate;
    protected mixed $endDate;
    protected int $diffDate;
    protected string $keyCache;

    /**
     * @param  array  $requestParams
     */
    public function __construct(array $requestParams)
    {
        $this->category = $requestParams['category'];
        $this->startDate = $requestParams['startDate'];
        $this->endDate = $requestParams['endDate'];
        $this->diffDate = $requestParams['diffDate'];
        $this->keyCache = sprintf("%s_%s_%s_%s",
            __CLASS__, $this->category, $this->startDate, $this->endDate);
    }

    /**
     * @param  array|null  $filters
     * @param  array|null  $sort
     * @param  int  $page
     * @param  int  $perPage
     * @return array
     * @throws BindingResolutionException
     */
    public function getProductsByCategoryFilters(
        array|null $filters,
        array|null $sort,
        int $page = 1,
        int $perPage = 100
    ): array {
        $category = $this->category;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $queryCondition = new QueryConditionCategoryProduct();

        $products = CardProduct::query()
            ->select([
                raw("0 as fbs"),
                raw("cp.vendor_code as sku"),
                raw("argMax(cp.name, cp.date) as name"),
                raw("argMax(cp.color, cp.date) as color"),
                raw("argMax(cp.category, cp.date) as category"),
                raw("argMax(cp.category_count, cp.date) as category_count"),
                raw("argMax(cp.pos, cp.date) as position"),
                raw("argMax(cp.brand_name, cp.date) as brand"),
                raw("argMax(cp.supplier_name, cp.date) as supplier"),
                raw("argMax(cp.grade, cp.date) as rating"),
                raw("max(cp.comments_count) as comments"),
                raw("argMax(cp.stock_count, cp.date) as stock"),
                raw("argMax(cp.final_price, cp.date) as last_price"),
                raw("min(cp.final_price) as min_price"),
                raw("max(cp.final_price) as max_price"),
                raw("intDivOrZero(revenue, total_sales) as avg_price"),
                raw("argMax(cp.price_u, cp.date) as base_price"),
                raw("argMax(cp.sale_price, cp.date) as base_sale"),
                raw("argMax(cp.sale_price_u, cp.date) as price_with_sale"),
                raw("argMax(cp.promo_sale, cp.date) as promo_sale"),
                raw("round(divide(revenue, days_in_stock) * $this->diffDate, 0) as revenue_potential"),
                raw("greatest(revenue_potential - revenue, 0) as lost_profit"),
                raw("round(divide(lost_profit, revenue_potential) * 100, 0) as lost_profit_percent"),
                raw("countIf(cp.current_sales, cp.current_sales > 0) as days_with_sales"),
                raw("countIf(cp.stock_count, cp.stock_count > 0) as days_in_stock"),
                raw("round(total_sales / $this->diffDate, 2) as sales_avg"),
                raw("sum(cp.current_sales) as total_sales"),
                raw("round(total_sales / days_in_stock, 2) as sales_in_stock_avg"),
                raw("sum(cp.revenue) as revenue"),
            ])
            ->fromCategory($category, $endDate, $startDate, 'cp')
            ->groupBy('cp.vendor_code');
        $queryCondition->prepare($products, $filters);

        $total = CardProduct::query()
            ->select([raw('count(*) as count')])
            ->from($products->getQuery())
            ->first()->count;

        if (!isset($sort)) {
            $sort['col'] = 'revenue';
            $sort['sort'] = 'desc';
        }

        $items = $products->orderBy($sort['col'], $sort['sort'])
            ->prepareForPaginate($page, $perPage)
            ->get();

        if ($items->count() > 0) {
            $resultVendorCode = $items->pluck('sku');
            $productsGraph = GraphForProductService::graphsForReport(
                $resultVendorCode,
                $this->startDate,
                $this->endDate
            );

            foreach ($items as &$item) {
                $wbImages = WildberriesHelper::generateWbImagesUrl($item->sku);

                $item->url = WildberriesHelper::generateProductUrl($item->sku);
                $item->image = $wbImages['small'];
                $item->image_middle = $wbImages['middle'];
                $item->graph_sales = explode(',', $productsGraph[$item->sku]->graph_sales);
                $item->graph_category_count = explode(',', $productsGraph[$item->sku]->graph_category_count);
                $item->graph_price = explode(',', $productsGraph[$item->sku]->graph_price);
                $item->graph_stock = explode(',', $productsGraph[$item->sku]->graph_stock);
            }
        }

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'page'
        ))->toArray();
    }

    /**
     * @param  array|null  $filters
     * @param  array|null  $sort
     * @param  int  $page
     * @param  int  $perPage
     * @return array
     */
    public function getCachedProducts(
        ?array $filters = null,
        ?array $sort = null,
        int $page = 1,
        int $perPage = 100
    ): array {
        return Cache::remember(
            md5(sprintf("%s_%s_%s", __METHOD__, $this->keyCache, serialize([$filters, $sort, $page, $perPage]))),
            Carbon::now()->secondsUntilEndOfDay(),
            function () use ($filters, $sort, $page, $perPage) {
                return $this->getProductsByCategoryFilters($filters, $sort, $page, $perPage);
            }
        );
    }

    /**
     * @return array
     * @throws ClientException
     */
    public function getSubcategoriesByCategory(): array
    {
        $selectItems = [
            'cp.category',
            raw('round(avg(cp.rating), 2) as avg_rating'),
            raw('round(avg(cp.comments), 2) as avg_comments'),
            raw('sum(cp.total_sales) as sales'),
            raw('intDivOrZero(sales, products) as sales_per_products_avg'),
            raw('intDivOrZero(sales, products_with_sales) as sales_per_products_with_sales_avg'),
            raw('sum(cp.revenue) as revenue'),
            raw('intDivOrZero(revenue, products) as revenue_per_products_avg'),
            raw('intDivOrZero(revenue, products_with_sales) as revenue_per_products_with_sales_avg'),
            raw('round(avg(cp.last_price), 2) as avg_price'),
            raw('count(cp.vendor_code) as products'),
            raw('countIf(cp.vendor_code, cp.total_sales > 0) as products_with_sales'),
            raw('round(products_with_sales / products * 100, 2) as products_with_sales_percent'),
            raw('count(DISTINCT cp.supplier) as suppliers'),
            raw('countIf(DISTINCT cp.supplier, cp.total_sales > 0) as suppliers_with_sales'),
            raw('round(suppliers_with_sales / suppliers * 100) as suppliers_with_sales_percent'),
            raw('count(DISTINCT cp.brand) as brands'),
            raw('countIf(DISTINCT cp.brand, cp.total_sales > 0) as brands_with_sales'),
            raw('round(brands_with_sales / brands * 100) as brands_with_sales_percent'),
        ];

        $subcategories = CardProduct::query()
            ->select($selectItems)
            ->fromSubcategory($this->category, $this->endDate, $this->startDate, 'cp')
            ->groupBy('cp.category')
            ->having('category', '!=', '')
            ->orderBy('revenue', 'desc')
            ->get();

        if ($subcategories->count() === 0) {
            $subQueryCategory = CardProduct::query()
                ->select([
                    'cp.vendor_code',
                    raw('argMax(cp.category_name, cp.date) as category'),
                    raw('argMax(cp.grade, cp.date) as rating'),
                    raw('argMax(cp.final_price, cp.date) as last_price'),
                    raw('argMax(cp.suppliers_id, cp.date) as supplier'),
                    raw('argMax(cp.brand_id, cp.date) as brand'),
                    raw('argMax(cp.comments_count, cp.date) as comments'),
                    raw('sum(cp.revenue) as revenue'),
                    raw('sum(cp.current_sales) as total_sales')
                ])
                ->from('card_products', 'cp')
                ->whereBetween('cp.date', [$this->startDate, $this->endDate])
                ->where('cp.category', '=', $this->category)
                ->groupBy('cp.vendor_code')
                ->getQuery();

            $subcategories = CardProduct::query()
                ->select($selectItems)
                ->from($subQueryCategory, 'cp')
                ->groupBy('cp.category')
                ->having('category', '!=', '')
                ->orderBy('revenue', 'desc')
                ->get();
        }

        return $subcategories->toArray();
    }

    /**
     * @return array
     */
    public function getCachedSubcategories(): array
    {
        return Cache::remember(
            md5(sprintf("%s_%s", __METHOD__, $this->keyCache)),
            Carbon::now()->secondsUntilEndOfDay(),
            function () {
                return $this->getSubcategoriesByCategory();
            }
        );
    }

    /**
     * @param  int  $segmentCount
     * @param  int|null  $minPrice
     * @param  int|null  $maxPrice
     * @return array
     * @throws ClientException
     */
    public function getPriceAnalysis(int $segmentCount, int $minPrice = null, int $maxPrice = null): array
    {
        if (!isset($minPrice) || !isset($maxPrice)) {
            $priceSegment = $this->getPriceSegment();
            if (empty($priceSegment)) {
                return [];
            }
            $minPrice = $priceSegment['minPrice'];
            $maxPrice = $priceSegment['maxPrice'];
        }

        $selectRange = QueryBuilderHelper::selectPriceRange($segmentCount, $minPrice, $maxPrice);
        $subQueryProductsWithSegment = CardProduct::query()
            ->select([
                'cp.vendor_code',
                raw('argMax(cp.brand_id, cp.`date`) as brand_id'),
                raw('argMax(cp.supplier_id, cp.`date`) as supplier_id'),
                raw('argMax(cp.final_price, cp.date) as last_price'),
                raw('sum(cp.revenue) as revenue'),
                raw('sum(cp.current_sales) as total_sales'),
                raw($selectRange),
                raw("extract(range, '^[^-]*') as min_range"),
                raw("extract(range, '\-(.+)') as max_range")
            ])
            ->fromCategoryPriceAnalysis($this->category, $this->endDate, $this->startDate, 'cp')
            ->groupBy('cp.vendor_code');

        return CardProduct::query()
            ->select([
                'products.range',
                raw('any(products.min_range) as min_range'),
                raw('any(products.max_range) as max_range'),
                raw('count(products.vendor_code) as products'),
                raw('countIf(products.vendor_code, products.total_sales > 0) as products_with_sales'),
                raw('round(avg(products.last_price), 2) as avg_price'),
                raw('sum(products.revenue) as revenue'),
                raw('intDivOrZero(revenue, products) as revenue_per_products_avg'),
                raw('intDivOrZero(revenue, products_with_sales) as revenue_per_products_with_sales_avg'),
                raw('sum(products.total_sales) as sales'),
                raw('intDivOrZero(sales, products) as sales_per_products_avg'),
                raw('intDivOrZero(sales, products_with_sales) as sales_per_products_with_sales_avg'),
                raw('count(DISTINCT products.supplier_id) as suppliers'),
                raw('countIf(DISTINCT products.supplier_id, products.total_sales > 0) as suppliers_with_sales'),
                raw('count(DISTINCT products.brand_id) as brands'),
                raw('countIf(DISTINCT products.brand_id, products.total_sales > 0) as brands_with_sales')
            ])
            ->from($subQueryProductsWithSegment->getQuery(), 'products')
            ->where('products.last_price', '>=', $minPrice)
            ->where('products.last_price', '<=', $maxPrice)
            ->groupBy('products.range')
            ->orderBy('avg_price')
            ->get()->toArray();
    }

    /**
     * @param  int  $segmentCount
     * @param  int|null  $minPrice
     * @param  int|null  $maxPrice
     * @return array
     */
    public function getCachedPriceAnalysis(int $segmentCount = 25, int $minPrice = null, int $maxPrice = null): array
    {
        return Cache::remember(
            md5(sprintf("%s_%s_%s",
                __METHOD__, $this->keyCache, serialize([$segmentCount, $minPrice, $maxPrice]))),
            Carbon::now()->secondsUntilEndOfDay(),
            function () use ($segmentCount, $minPrice, $maxPrice) {
                return $this->getPriceAnalysis($segmentCount, $minPrice, $maxPrice);
            }
        );
    }

    /**
     * @throws ClientException
     */
    #[ArrayShape(['maxPrice' => "int", 'minPrice' => "int"])]
    public function getPriceSegment(): array
    {
        $priceSegment = CardProduct::query()
            ->select([
                raw('maxOrNull(cp.final_price) as maxPrice'),
                raw('minOrNull(cp.final_price) as minPrice')
            ])
            ->from(function ($from) {
                $from->query()
                    ->select([
                        'cp.vendor_code',
                        raw('argMax(cp.final_price, cp.date) as final_price')
                    ])
                    ->from('card_products', 'cp')
                    ->where("cp.category", 'LIKE', sprintf("%s%%", $this->category))
                    ->whereBetween('cp.date', [$this->startDate, $this->endDate])
                    ->groupBy('cp.vendor_code');
            }, 'cp')
            ->where('cp.final_price', '!=', 0)
            ->get()
            ->first();

        return isset($priceSegment->maxPrice) ? $priceSegment->toArray() : [];
    }
}