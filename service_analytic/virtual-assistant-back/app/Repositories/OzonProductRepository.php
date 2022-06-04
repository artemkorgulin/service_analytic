<?php

namespace App\Repositories;

use App\Models\OzProduct;
use App\Models\OzProductTop36;
use App\Models\OzTemporaryProduct;
use App\Models\ProductDashboard;
use App\Models\WebCategory;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Services\V2\OzonListProductsService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Contracts\Repositories\CommonProductRepositoryInterface;
use App\Repositories\Common\CommonSegmentRepository;
use App\Repositories\Common\CommonProductRepository;

class OzonProductRepository implements CommonProductRepositoryInterface
{
    const QUERY_FILTER_PARAMS = [
        'with_features',
        'with_category',
        'order',
        'filter',
        'filter_operator',
        'select',
        'where_between',
        'search',
    ];

    private Builder $builder;

    /**
     * Get Ozon product
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getProduct($id)
    {
        return OzProduct::currentUser()->currentAccount()->firstWhere('id', $id);
    }

    /**
     * Get Ozon Web-Category
     * @param $web_category_id
     * @return mixed
     */
    public static function getWebCategory($web_category_id)
    {
        return WebCategory::query()->where('id', $web_category_id)->first();
    }

    /**
     * Get Ozon product top 36
     * @param $external_id
     * @return mixed
     */
    public static function getProductTop36($external_id, $type = 'min')
    {
        $lastDateFor = OzProductTop36::query()
            ->where([
                'web_category_id' => $external_id,
                'type' => $type,
            ])->max('parsed_at');
        return OzProductTop36::query()
            ->where([
                'web_category_id' => $external_id,
                'type' => $type,
                'parsed_at' => $lastDateFor
            ])->first();
    }

    /**
     * @param  int  $productId
     * @return OzProduct
     * @throws \Exception
     */
    public function getProductDataToSendInOzon(int $productId): OzProduct
    {
        $getProduct = OzProduct::query()
            ->where('id', $productId)
            ->with('featuresValues')
            ->get()
            ->first();

        if (!$getProduct) {
            throw new \Exception('Product does not exist or has been deleted.');
        }

        return $getProduct;
    }

    /**
     * @param  int  $userId
     * @param  array|null  $query
     * @return void
     */
    public function setQueryFilter(
        int $userId,
        int $accountId,
        array $query = null,
    ) {
        $this->builder = OzProduct::query()
            ->CurrentUserAndAccountByParam($userId, $accountId);

        if ($query) {
            $this->queryParamBuilder($query);
        }
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->builder->get();
    }

    /**
     * @param $blackListBrands
     * @param  string  $type
     * @param $availability
     * @param $search
     * @return mixed
     */
    public static function getNotActiveBrands($blackListBrands, string $type, $availability, $search): mixed
    {
        if ($type === 'fbs') {
            $skuField = 'sku_fbs';
            $quantityField = 'quantity_fbs';
        } else {
            $skuField = 'sku_fbo';
            $quantityField = 'quantity_fbo';
        }

        $query = OzTemporaryProduct::query()
            ->select('brand', DB::raw("COUNT(DISTINCT($skuField)) AS qty"))
            ->whereNotIn('external_id', OzonListProductsService::getAccountObservedProductExternalIds())
            ->where('account_id', UserService::getAccountId())
            ->whereNotIn('brand', $blackListBrands)
            ->groupBy('brand')
            ->orderBy('brand', 'ASC');

        if ($availability == 1) {
            $query->whereRaw(sprintf('%s > 0', $quantityField));
        } elseif ($availability == 2) {
            $query->whereRaw(sprintf('(%1$s = 0 OR %1$s IS NULL)', $quantityField));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%");
                if ((int) $search !== 0) {
                    $q->orWhere('sku_fbo', (int) $search)
                        ->orWhere('sku_fbs', (int) $search);
                }
            });
            $paginate = 999;
        } else {
            $paginate = null;
        }

        $skuUsed = OzProduct::getSkuUsed($skuField);
        if (!empty($skuUsed)) {
            $query = $query->whereNotIn($skuField, $skuUsed);
        }

        return $query->paginate($paginate)->setPath('');
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->builder;
    }

    /**
     * @TODO Except non usage fields
     * @return void
     */
    public function setWithFeature(): void
    {
        $this->builder->with([
            'featuresValues' => function ($query) {
                $query->select('id', 'product_id', 'value', 'feature_id', 'option_id');
            }
        ]);
    }

    /**
     * @TODO Except non usage fields
     * @return void
     */
    public function setWithCategory(): void
    {
        $this->builder->with([
            'category' => function ($query) {
                $query->select('id', 'name');
            }
        ]);
    }

    /**
     * @param  array  $ids
     * @return \Illuminate\Support\Collection
     */
    public function getProductCollectionByIds(array $ids): \Illuminate\Support\Collection
    {
        return OzProduct::query()->whereIn('id', $ids)->get();
    }

    /**
     * @TODO Add operator in where case, cut logic into method
     *
     * @param  array  $queryParams
     * @return void
     */
    public function queryParamBuilder(array $queryParams)
    {
        foreach ($queryParams as $key => $param) {
            if (!in_array($key, self::QUERY_FILTER_PARAMS)) {
                continue;
            }

            switch ($key) {
                case 'order':
                    foreach ($param as $keyOrder => $orderValue) {
                        $this->builder->orderBy($keyOrder, $orderValue);
                    }
                    break;

                case 'filter':
                    foreach ($param as $keyFilter => $filterVal) {
                        if (!is_array($filterVal)) {
                            $this->builder->where(
                                $keyFilter,
                                $filterVal
                            );
                        }

                        if (is_array($filterVal)) {
                            $this->builder->whereIn(
                                $keyFilter,
                                $filterVal
                            );
                        }
                    }
                    break;

                case 'with_features':
                    if ($param) {
                        $this->setWithFeature();
                    }
                    break;

                case 'with_category':
                    if ($param) {
                        $this->setWithCategory();
                    }
                    break;

                case 'select':
                    $this->builder->select($param);
                    break;

                case 'where_between':
                    foreach ($param as $key => $between) {
                        $this->builder->whereBetween($key, [$between[0], $between[1]]);
                    }
                    break;

                case 'search':
                    $this->search($param);
            }
        }
    }

    /**
     * Search by multiple fields (name, id, external_id, sku_fbo, sku_fbs)
     * @param  string  $param
     * @return void
     */
    private function search(string $param)
    {
        $this->builder->where(function ($query2) use ($param) {
            $query2->where('name', 'LIKE', '%'.$param.'%')
                ->orWhere('id', $param)
                ->orWhere('external_id', 'LIKE', '%'.$param.'%')
                ->orWhere('sku_fbo', 'LIKE', '%'.$param.'%')
                ->orWhere('sku_fbs', 'LIKE', '%'.$param.'%');
        });
    }

    /**
     * @param  array  $blackListBrands
     * @param  string  $type
     * @param  int  $availability
     * @param  string  $search
     * @param  string  $brand
     * @return \Illuminate\Support\Collection
     */
    public static function getNotActiveProducts(
        array $blackListBrands,
        string $type,
        int $availability,
        string $search,
        string $brand
    ): \Illuminate\Support\Collection {
        if ($type === 'fbs') {
            $skuField = 'sku_fbs';
            $quantityField = 'quantity_fbs';
        } else {
            $skuField = 'sku_fbo';
            $quantityField = 'quantity_fbo';
        }

        $query = OzTemporaryProduct::query()
            ->select([
                'id',
                'title',
                sprintf('%s AS sku', $skuField),
                'barcode',
                'brand',
                'image',
                sprintf('%s AS quantity', $quantityField),
                'sku_fbo AS url_id'
            ])
            ->whereNotIn('external_id', OzonListProductsService::getAccountObservedProductExternalIds())
            ->where('account_id', UserService::getAccountId())
            ->whereNotIn('brand', $blackListBrands)
            ->orderBy('title', 'ASC');

        if ($availability == 1) {
            $query->whereRaw(sprintf('%s > 0', $quantityField));
        } elseif ($availability == 2) {
            $query->whereRaw(sprintf('(%1$s = 0 OR %1$s IS NULL)', $quantityField));
        }

        if ($search || $brand) {
            $search = escapeRawQueryString($search);
            $brand = escapeRawQueryString($brand);
            if ($search) {
                $query = $query->where(function ($query) use ($skuField, $search, $brand) {
                    $query->where('brand', 'LIKE', "%{$brand}%")
                        ->where(function ($query) use ($search) {
                            $query->orWhere('brand', 'LIKE', "%{$search}%")
                                ->orWhere('title', 'LIKE', "%{$search}%")
                                ->orWhere('sku_fbo', 'LIKE', "%{$search}%")
                                ->orWhere('sku_fbs', 'LIKE', "%{$search}%");
                        });
                });
            } elseif ($brand) {
                $query->where('brand', 'LIKE', "%{$brand}%");
            }
            $paginate = 999;
        } else {
            $paginate = null;
        }

        $custom = collect(['totalCount' => $query->count()]);
        return $custom->merge($query->paginate($paginate)->setPath(''));
    }

    /**
     * @return void
     */
    public function setMassUpdateProductField()
    {
        $this->builder->select(
            'id',
            'oz_products.external_id',
            'name',
            'url',
            'status_id',
            'photo_url',
            'brand',
            'color_image',
            'price',
            'sku_fbo',
            'sku_fbs',
            'content_optimization',
            'category_id',
            'price',
            'old_price',
            'optimization',
        );
    }

    /**
     * @return void
     */
    public function setListViewProductFields()
    {
        $this->builder->select(
            'id',
            'oz_products.external_id',
            'sku_fbo',
            'sku_fbs',
            'name',
            'brand',
            'optimization',
            'rating',
            'count_reviews',
            'updated_at',
            'price',
            'old_price',
            'premium_price',
            'photo_url',
            'card_updated',
            'is_test',
            'characteristics_updated_at',
            'characteristics_updated',
            'position_updated',
            'mpstat_updated_at',
            'content_optimization',
            'search_optimization',
            'visibility_optimization',
            'quantity_fbo',
            'quantity_fbs',
            'created_at',
        );
    }

    /**
     * @return void
     */
    public function addSelectFormattedFields()
    {
        $this->builder->addSelect(
            DB::raw('FORMAT(price, 2) as price,
                           FORMAT(old_price, 2) as old_price,
                           FORMAT(premium_price, 2) as premium_price,
                           url as product_ozon_url')
        );
    }

    /**
     * @return void
     */
    public function initQueryBuilder()
    {
        $this->builder = OzProduct::query();
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

        $query = [
            'filter' => [
                'oz_products.id' => $ids,
            ],
        ];

        $this->initQueryBuilder();
        $this->queryParamBuilder($query);
        $this->builder->with('category');
        $this->builder->with('status');
        $this->setListViewProductFields();
        $this->addSelectFormattedFields();
        $query = $this->getQueryBuilder();
        $query->currentUserAndAccount();

        return $query;
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

        $segmentRange = CommonSegmentRepository::getSermentationOptimizationRangeByType($segmentType);
        $query = [
            'where_between' => [
                'optimization' => [
                    $segmentRange[0],
                    $segmentRange[1],
                ]
            ],
        ];

        $this->initQueryBuilder();
        $this->queryParamBuilder($query);
        $this->builder->with('category');
        $this->builder->with('status');
        $this->setListViewProductFields();
        $this->addSelectFormattedFields();
        $query = $this->getQueryBuilder();
        $query->currentUserAndAccountByParams($dashboard->user_id, $dashboard->account_id);

        return $query;
    }
}
