<?php

namespace App\Repositories\Wildberries;

use App\Models\ProductDashboard;
use App\Models\WbCategory;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Services\UserService;
use App\Services\Wildberries\WilberriesListProductsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Repositories\CommonProductRepositoryInterface;
use App\Repositories\Common\CommonSegmentRepository;
use App\Repositories\Common\CommonProductRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WildberriesProductRepository implements WildberriesProductRepositoryInterface, CommonProductRepositoryInterface
{
    private static array $WildberriesProductSortingMapper = [
        'title' => 'wb_products.title',
        'brand' => 'wb_products.brand',
        'position' => 'id',
        'rating' => 'wb_products.rating',
        'price' => 'price',
        'status_id' => 'status_id',
        'characteristics' => 'optimization',
        'optimization' => 'wb_products.optimization',
    ];

    const SELECT_PRODUCT_LIST_WITHOUT_CHARACTERISTICS = [
        'id', 'card_id', 'wb_products.imt_id', 'card_user_id', 'supplier_id', 'imt_supplier_id',
        'title', 'brand', 'sku', 'nmid', 'barcodes', 'image', 'price', 'object', 'parent', 'country_production',
        'supplier_vendor_code', 'data_nomenclatures', 'dimension_unit', 'depth', 'height', 'width', 'weight_unit',
        'weight', 'data', 'count_reviews', 'is_test', 'is_notificated', 'status', 'status_id',
        'rating', 'price_range', 'url', 'optimization', 'object', 'quantity'
    ];

    private Builder $builder;

    public function getSortedProducts(
        $categoryIds,
        $search,
        $sortBy,
        $sortType,
        $categoryNames = '',
        $withCharacteristics = false,
        $availability = 0
    ) {
        $select = $withCharacteristics ? 'wb_products.*' : self::SELECT_PRODUCT_LIST_WITHOUT_CHARACTERISTICS;
        $products = WbProduct::select($select)
            ->distinct('sku')
            ->currentUserAndAccount()
            ->with('nomenclatures');

        $availability = (int) $availability;
        if ($availability === 2) {
            $products = $products->where('quantity', 0);
        } elseif ($availability === 1) {
            $products = $products->where('quantity', '>', 0);
        }

        if ($categoryIds) {
            $categories = WbCategory::whereIn('id', $categoryIds)->pluck('name');
            $products->whereIn('object', $categories);
        }

        if ($categoryNames) {
            $products->whereIn('object', explode(',', $categoryNames));
        }

        if ($search) {
            $searchLike = sprintf('%%%s%%', trim($search));
            $selectedAccountId = (new UserService())->getAccountId();
            $products = $products->where('wb_products.account_id', $selectedAccountId)->where(function ($query) use (
                $searchLike
            ) {
                $query->where('title', 'LIKE', $searchLike)
                    ->orWhere('nmid', 'LIKE', $searchLike)
                    ->orWhere('brand', 'LIKE', $searchLike)
                    ->orWhere('wb_products.imt_id', 'LIKE', $searchLike)
                    ->orWhere('object', 'LIKE', $searchLike)
                    ->orWhere('data_nomenclatures', 'LIKE', $searchLike);
            });
        }

        if ($sortBy && $sortType && in_array($sortBy, array_keys(self::$WildberriesProductSortingMapper))) {
            $sortBy = self::$WildberriesProductSortingMapper[$sortBy];
            if ($sortBy == 'price' || $sortBy == 'price_with_discount') {
                $products = $products->selectRaw('
                    CASE
                        WHEN JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount") IS NULL
                            THEN price
                        WHEN JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount") = 0
                            THEN price
                        ELSE
                            (100 - JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount")) / 100 * price
                    END as price_with_discount
                ');
                $products = $products->orderBy('price_with_discount', $sortType);
            } else {
                $products = $products->orderBy($sortBy, $sortType);
            }
        }

        return $products;
    }

    /**
     * Searching for none-active products of brand
     *
     * @param  string  $search
     * @param  string  $brand
     * @param  int  $accountId
     * @param  array  $blackListBrands
     * @param  int  $availability
     * @return Collection
     */
    public function selectNotActiveProductsByTextAndBrand(
        string $search,
        string $brand,
        int $accountId,
        array $blackListBrands = [],
        int $availability = 0
    ): Collection {
        $query = WbTemporaryProduct::select([
            'id', 'title', 'sku', 'barcodes AS barcode', 'brand', 'url', 'image', 'quantity'
        ])
            ->whereNotIn('imt_id', WilberriesListProductsService::getAccountObservedProductImtIds())->distinct('sku');
        $search = escapeRawQueryString($search);
        $brand = escapeRawQueryString($brand);

        if ($search) {
            $query = $query->where(function ($query) use ($search, $brand) {
                $query->where('brand', 'LIKE', "%{$brand}%")
                    ->where(function ($query) use ($search) {
                        $query->orWhere('brand', 'LIKE', "%{$search}%")
                            ->orWhere('title', 'LIKE', "%{$search}%")
                            ->orWhere('sku', 'LIKE', "%{$search}%")
                            ->orWhere('nomenclatures', 'LIKE', "%{$search}%");
                    });
            });
        } elseif ($brand) {
            $query->where('brand', $brand);
        }

        // наличие
        if ($availability === 2) {
            $query = $query->where('quantity', 0);
        } elseif ($availability === 1) {
            $query = $query->where('quantity', '>', 0);
        }

        // exclude articles (sku)
        $skuUsed = WbProduct::getSkuUsed();
        if (!empty($skuUsed)) {
            $query = $query->whereNotIn('sku', $skuUsed);
        }

        $query = $query->where('account_id', $accountId)
            ->whereNotIn('brand', $blackListBrands);
        $countQuery = $query->count();

        // sorting and pagination
        $paginate = ($search || $brand) ? 999 : null;
        $query = $query->groupBy('sku')
            ->orderBy('title', 'ASC')
            ->paginate($paginate)
            ->setPath('');

        $custom = collect(['totalCount' => $countQuery]);
        return $custom->merge($query);
    }

    /**
     * Searching for none-active products
     *
     * @param  string  $search  - search query
     * @param  int  $accountId  - account ID
     * @param  array  $blackListBrands  - array of brands in Black-list
     * @param  int  $availability  - available of products
     * @return mixed
     */
    public function selectNotActiveBrandsWithFilters(
        string $search = '',
        int $accountId = 0,
        array $blackListBrands = [],
        int $availability = 0
    ): mixed {
        $query = WbTemporaryProduct::select(
            'brand',
            DB::raw("COUNT(DISTINCT(sku)) AS qty"),
        );

        if ($availability === 2) {
            $query = $query->where('quantity', 0);
        } elseif ($availability === 1) {
            $query = $query->where('quantity', '>', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%");
                if ((int) $search !== 0) {
                    $q->orWhere('sku', (int) $search);
                }
            });
            $paginate = 999;
        } else {
            $paginate = null;
        }

        // exclude articles (sku)
        $skuUsed = WbProduct::getSkuUsed();
        if (!empty($skuUsed)) {
            $query = $query->whereNotIn('sku', $skuUsed);
        }

        $query->where('account_id', $accountId)
            ->groupBy('brand')
            ->whereNotIn('brand', $blackListBrands)
            ->orderBy('brand', 'ASC');

        return $query->paginate($paginate)->setPath('');
    }

    /**
     * {Inherit}
     * @see CommonProductRepositoryInterface::getProductQueryByIds()
     */
    public function getProductQueryByIds(
        array $ids,
        ?string $sortBy = '',
        ?string $sortType = ''
    ): Builder {

        $products = WbProduct::query()
            ->select('wb_products.*')
            ->distinct('sku')
            ->with('nomenclatures')
            ->currentUserAndAccount()
            ->whereIn('id', $ids);

        if ($sortBy && $sortType && in_array($sortBy, array_keys(self::$WildberriesProductSortingMapper))) {
            $products = $this->getProductSortingQueryBuilder($products, $sortBy, $sortType);
        }

        return $products;
    }

    /**
     * @param  Builder  $builder
     * @param  string  $sortBy
     * @param  string  $sortType
     * @return Builder
     */
    private function getProductSortingQueryBuilder(
        Builder $builder,
        string $sortBy,
        string $sortType
    ): Builder {
        $sortBy = self::$WildberriesProductSortingMapper[$sortBy];
        if ($sortBy === 'price' || $sortBy === 'price_with_discount') {
            $builder = $builder->selectRaw('
                    CASE
                        WHEN JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount") IS NULL
                            THEN price
                        WHEN JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount") = 0
                            THEN price
                        ELSE
                            (100 - JSON_EXTRACT(wb_products.data_nomenclatures, "$[0].discount")) / 100 * price
                    END as price_with_discount
                ');
            $builder = $builder->orderBy('price_with_discount', $sortType);
        } else {
            $builder = $builder->orderBy($sortBy, $sortType);
        }

        return $builder;
    }

    public function getCountOfDeletedProducts()
    {
        return WbProduct::query()
            ->select('id')
            ->currentUser()
            ->currentAccount()
            ->onlyTrashed()
            ->count('id');
    }

    /**
     * @param  int  $userId
     * @param  int  $accountId
     * @param  array  $optimizationRange
     * @return Builder
     */
    public function getProductsByOptimizationRange(int $userId, int $accountId, array $optimizationRange): Builder
    {
        return WbProduct::query()
            ->select('wb_products.*')
            ->distinct('sku')
            ->currentUserAndAccountByParams($userId, $accountId)
            ->with('nomenclatures')
            ->whereBetween('optimization', [$optimizationRange[0], $optimizationRange[1]]);
    }

    /**
     * {Inherit}
     * @see CommonProductRepositoryInterface::getProductQueryByDashboardSegmentation()
     */
    public function getProductQueryByDashboardSegmentation(
        ProductDashboard $dashboard,
        string $segmentType,
        ?string $sortBy = '',
        ?string $sortType = ''
    ): Builder {
        return $this->getProductsByOptimizationRange(
            $dashboard->user_id,
            $dashboard->account_id,
            CommonSegmentRepository::getSermentationOptimizationRangeByType($segmentType)
        );
    }

    /**
     * @param  int  $marketplaceId
     * @param  array  $productIds
     * @param  string|null  $sortBy
     * @param  string|null  $sortType
     * @return Builder
     */
    public function getProductsQueryByDashboardSegmentation(
        ProductDashboard $productDashboard,
        string $segmentType,
        ?string $sortBy = '',
        ?string $sortType = ''
    ): Builder {
        $segment = CommonProductDashboardRepository::dashboardDBSegmentMapper($segmentType);
        $checkFilter = config('model.dashboard.type.'.$productDashboard->dashboard_type.'.range_filter');
        $products = null;

        if ($checkFilter === true) {
            $products = $this->getProductQueryByDashboardSegmentation(
                $productDashboard,
                $segmentType,
                $sortBy,
                $sortType
            );
        }

        if (isset($productDashboard->$segment['product_ids'])) {
            $products = $this->getProductQueryByIds(
                $productDashboard->$segment['product_ids'],
                $sortBy,
                $sortType
            );
        }

        if (!$products) {
            throw new \Exception('Empty products filter.');
        }

        return $products;
    }
}
