<?php

namespace App\Models;

use App\Classes\Helper;
use App\Repositories\Common\CommonProductRepository;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class WbProduct extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ERROR = 3;
    const STATUS_SUCCESS = 1;
    const STATUS_EDITED_LOCAL = 5;

    const STATUS_EDITED_LOCAL_TEXT = 'Отредактирован локально';

    public $fillable = [
        'user_id', 'account_id', 'card_id', 'imt_id', 'card_user_id', 'supplier_id', 'imt_supplier_id', 'title', 'brand',
        'sku', 'nmid', 'barkodes', 'image', 'price', 'object', 'parent', 'country_production', 'supplier_vendor_code',
        'data', 'data_nomenclatures', 'recommendations', 'recommended_characteristics', 'required_characteristics', 'dimension_unit', 'depth', 'height',
        'width', 'weight_unit', 'weight', 'count_reviews', 'is_test', 'is_notificated', 'status',
        'status_id', 'rating', 'price_range', 'url', 'optimization', 'quantity',
    ];

    public $appends = ['photos360', 'video'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'data' => 'object',
        'data_nomenclatures' => 'object',
        'recommendations' => 'object',
        'recommended_characteristics' => 'object',
        'required_characteristics' => 'object',
        'barcodes' => 'object',
        'price' => 'float',
        'price_range' => 'object',
        'rating' => 'float',
        'optimization' => 'float'
    ];

    /**
     * Create WB product
     * @param WbTemporaryProduct $temporaryProduct
     * @param $account
     * @return static
     */
    public static function createWbProduct(WbTemporaryProduct $temporaryProduct, $account): self
    {
        // Initially create and upload temporary product
        return self::create([
            'account_id' => $temporaryProduct->account_id,
            'user_id' => $account['pivot']['user_id'],
            'card_id' => $temporaryProduct->card_id,
            'imt_id' => $temporaryProduct->imt_id,
            'card_user_id' => $temporaryProduct->card_user_id,
            'supplier_id' => $temporaryProduct->supplier_id,
            'imt_supplier_id' => $temporaryProduct->imt_supplier_id,
            'title' => $temporaryProduct->title,
            'object' => $temporaryProduct->object,
            'parent' => $temporaryProduct->parent,
            'country_production' => $temporaryProduct->country_production,
            'supplier_vendor_code' => $temporaryProduct->supplier_vendor_code,
            'data' => $temporaryProduct->data,
            'price_range' => Helper::wbCardGetPriceRangeArray($temporaryProduct->data),
        ]);
    }

    /**
     * Create WB product with user Id and account Id
     * @param WbTemporaryProduct $temporaryProduct
     * @param $account
     * @return static
     */
    public static function createWbProductWithUserAndAccount(WbTemporaryProduct $temporaryProduct, $userId, $accountId): self
    {

        $data = Helper::getUsableData($temporaryProduct);

        // Initially create and upload temporary product
        $newProduct = self::create([
            'account_id' => $temporaryProduct->account_id,
            'user_id' => $userId,
            'card_id' => $temporaryProduct->card_id,
            'imt_id' => $temporaryProduct->imt_id,
            'card_user_id' => $temporaryProduct->card_user_id,
            'supplier_id' => $temporaryProduct->supplier_id,
            'imt_supplier_id' => $temporaryProduct->imt_supplier_id,
            'title' => $temporaryProduct->title,
            'object' => $temporaryProduct->object,
            'parent' => $temporaryProduct->parent,
            'country_production' => $temporaryProduct->country_production,
            'supplier_vendor_code' => $temporaryProduct->supplier_vendor_code,
            'data' => $data,
            'price_range' => Helper::wbCardGetPriceRangeArray($data),
        ]);

        DB::table('wb_product_user')->updateOrInsert([
            'user_id' => $userId,
            'account_id' => $accountId,
            'imt_id' => $temporaryProduct->imt_id,
        ], [
            'deleted_at' => null,
        ]);

        return $newProduct;
    }

    /**
     * Find one product from current user
     *
     * @param $id
     * @return mixed
     */
    public static function findWithCurrentUser($id)
    {
        return self::currentUser()->find($id);
    }

    /**
     * Get current user
     * Attention! Works ONLY for http (not for cli)
     * @return mixed
     * @throws \Exception
     */
    public static function currentUser()
    {
        if (app()->runningInConsole() === true) {
            throw new \Exception('Функция currentUser не работает из командной строки!', 500);
        }
        $userId = UserService::getUserId();
        return self::where('user_id', $userId);
    }

    /**
     * Возврат booted метода
     */
    protected static function booted()
    {
        self::created(function ($product) {
            $product = self::_update($product);
            $product->save();
        });

        self::updating(function ($product) {
            try {
                $product = self::_update($product);
            } catch (\Exception $exception) {
                report($exception);
            }
        });
    }

    /**
     * Update product
     * @param $product
     * @return mixed
     */
    private static function _update(&$product)
    {
        $nomenclatures = $product->nomenclatures()->get()->toArray();
        $data = Helper::getUsableData($product);

        $product->title = Helper::wbCardGetTitle($data);

        $product->quantity = self::getQuantity($data);

        foreach ($data->nomenclatures as $key => &$nm) {
            foreach ($nomenclatures as $nomenclature) {
                if ($nm->nmId === $nomenclature['nm_id']) {
                    $data->nomenclatures[$key]->price = $nomenclature['price'];
                    $data->nomenclatures[$key]->discount = $nomenclature['discount'];
                    $data->nomenclatures[$key]->promocode = $nomenclature['promocode'];
                }
            }
        }

        // delete null units in "addin" data array
        // Set product price (for index products view)
        $product->price = Helper::wbCardGetPrice($data);

        // Set product price range
        $product->price_range = Helper::wbCardGetPriceRangeArray($data);

        // Set product brand
        $product->brand = Helper::wbCardGetBrand($data);

        // Set product photo
        $product->image = Helper::wbCardGetPhoto($data);

        // Set data nomenclatures
        if (isset($data->nomenclatures)) {
            // Merge data_nomenclatures and nomenclatures (for prices, discount and promocodes)
            foreach ($data->nomenclatures as $key => $nomenclature) {
                if (is_object($nomenclature) && isset($nomenclatures[array_search($nomenclature->nmId, $nomenclatures)])) {
                    $foundNm = $nomenclatures[array_search($nomenclature->nmId, $nomenclatures)];
                    $data->nomenclatures[$key]->price = $foundNm['price'] ?? 0;
                    $data->nomenclatures[$key]->discount = $foundNm['discount'] ?? 0;
                    $data->nomenclatures[$key]->promocode = $foundNm['promocode'] ?? 0;
                } elseif (is_array($nomenclature) && isset($nomenclatures[array_search($nomenclature['nmId'], $nomenclatures)])) {
                    $foundNm = $nomenclatures[array_search($nomenclature['nmId'], $nomenclatures)];
                    $data->nomenclatures[$key]['price'] = $foundNm['price'] ?? 0;
                    $data->nomenclatures[$key]['discount'] = $foundNm['discount'] ?? 0;
                    $data->nomenclatures[$key]['promocode'] = $foundNm['promocode'] ?? 0;
                }
            }
            $product->data_nomenclatures = $data->nomenclatures;
        }

        if ($product->status_id) {
            $product->status = optional(WbProductStatus::find($product->status_id))->name ?? '';
        }

        // Set product url
        $product->url = Helper::wbCardGetUrl($data);

        // Set sku and first nomenclature value
        if (isset($data->nomenclatures[0]->nmId)) {
            $product->sku = $data->nomenclatures[0]->nmId;
            $product->nmid = $data->nomenclatures[0]->nmId;
        }

        // todo refactor this when get full technical consultation
        if (is_null($product->is_test)) {
            $product->is_test = false;
        }
        if (is_null($product->is_notificated)) {
            $product->is_notificated = false;
        }
        // Barcodes get
        $product->barcodes = Helper::wbCardGetBarcodes($data);

        // Get and set parameter 'Ширина упаковки'
        $product->width = Helper::wbCardGetPackingWidth($data);

        // Get and set parameter 'Глубина упаковки'
        $product->depth = Helper::wbCardGetPackingDepth($data);

        // Get and set parameter 'Глубина упаковки'
        $product->height = Helper::wbCardGetPackingDepth($data);

        // Get and set parameter dimension_unit
        $product->weight_unit = Helper::wbCardGetPackingDimentionUnits($data);

        // Get and set parameter 'Вес товара с упаковкой (г)'
        $product->weight = Helper::wbCardGetPackingWeight($data);

        // Get and set parameter weight_unit
        $product->weight_unit = Helper::wbCardGetPackingWeightUnits($data);

        $category = WbCategory::firstWhere(['name' => $product->object, 'parent' => $product->parent]);
        if ($category) {
            $tmpCharacteristics = $category->data;
            $product->recommended_characteristics = $tmpCharacteristics;
            $requiredCharacteristics = [];
            if (!isset($product->recommended_characteristics) || !$product->recommended_characteristics) {
                return $product;
            }
            foreach ($product->recommended_characteristics->addin as $characteristic) {
                $find = false;
                $data = (object)$data;
                foreach ($data->addin as $addin) {
                    if ($addin->type === $characteristic->type) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $requiredCharacteristics[] = $characteristic;
                }
            }
            $product->required_characteristics = $requiredCharacteristics;
        }

        $product->data = (object)$product->data;

        if (!isset($product->data->id) || !$product->data->id) {
            $product->data->id = $product->card_id;
        }

        if (!isset($product->data->imtId) || !$product->data->imtId) {
            $product->data->imtId = $product->imt_id;
        }

        if (!isset($product->data->object) || !$product->data->object) {
            $product->data->object = $product->object;
        }

        if (!isset($product->data->parent) || !$product->data->parent) {
            $product->data->parent = $product->parent;
        }

        if (!isset($product->data->userId) || !$product->data->userId) {
            $product->data->userId = 0;
        }

        if (!isset($product->data->batchID) || !$product->data->batchID) {
            $product->data->batchID = "00000000-0000-0000-0000-000000000000";
        }

        if (!isset($product->data->supplierId) || !$product->data->supplierId) {
            $product->data->supplierId = $product->supplier_id;
        }

        if (!isset($product->data->imtSupplierId) || !$product->data->imtSupplierId) {
            $product->data->supplierId = $product->imt_supplier_id;
        }

        if (!isset($product->data->supplierVendorCode) || !$product->data->supplierVendorCode) {
            $product->data->supplierVendorCode = $product->supplier_vendor_code;
        }

        return $product;
    }

    /**
     * Quantity in stocks (summary)
     * @return int
     */
    public static function getQuantity($data): int
    {
        $barcodes = Helper::wbCardGetBarcodes($data);
        $today = date('Y-m-d', time());
        $stocksQty = WbBarcodeStock::whereIn('barcode', $barcodes)->where('check_date', $today)->sum('quantity') ?? 0;
        if (!$stocksQty) {
            $nomenclatures = Helper::wbCardGetNmIds($data);
            $stocksQty = WbNomenclature::whereIn('nm_id', $nomenclatures)->sum('quantity') ?? 0;
        }
        return (int)$stocksQty;
    }

    /**
     * Gel list of used SKU
     *
     * @return array
     */
    public static function getSkuUsed(): array
    {
        $skuUsed = [];
        $products = self::select('sku')
            ->currentUser()
            ->currentAccount()
            ->get();
        if (!$products->isEmpty()) {
            $skuUsed = $products->pluck('sku')->all();
        }
        return $skuUsed;
    }

    /**
     * Return keywords for product
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keywords()
    {
        return $this->hasMany(WbPickList::class, 'wb_product_id', 'id');
    }

    /**
     * Escrow history
     *
     * @return HasMany
     */
    public function escrowHistory(): HasMany
    {
        return $this->hasMany(EscrowHistory::class, 'product_id', 'id');
    }

    /**
     * Return card nomenclatures
     * @return BelongsToMany
     */
    public function nomenclatures()
    {
        return $this->belongsToMany(WbNomenclature::class, 'wb_product_nomenclatures');
    }

    /**
     * Return category for product
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->hasOne(WbCategory::class, 'name', 'object');
    }

    /**
     * Return photo 360
     * @return array|mixed|null
     */
    public function getPhotos360Attribute()
    {
        return Helper::wbCardGetPhotos360($this->data);
    }

    /**
     * Return images
     * @return array|mixed|null
     */
    public function getImagesAttribute()
    {
        $images = [];
        try {
            foreach ($this->data_nomenclatures as $data) {
                foreach ($data->addin as $addin) {
                    if ($addin->type !== 'Фото') {
                        continue;
                    }
                    foreach ($addin->params as $param) {
                        $images[] = $param->value;
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);
        }
        return $images;
    }

    /**
     * Return images
     * @return array|mixed|null
     */
    public function getNmidsAttribute()
    {
        $nmIds = [];
        try {
            foreach ($this->data_nomenclatures as $data) {
                $nmIds[] = $data->nmId;
            }
        } catch (\Exception $exception) {
            report($exception);
            return [];
        }
        return $nmIds;
    }

    /**
     * Return photo 360
     * @return array|mixed|null
     */
    public function setPhotos360Attribute($photos)
    {
        $this->data = Helper::wbCardSetPhotos360($this->data, $photos);
        return $photos;
    }

    /**
     * Return Video
     * @return array|mixed|null
     */
    public function getVideoAttribute()
    {
        return Helper::wbCardGetVideo($this->data);
    }

    /**
     * Get products from current account
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query)
    {
        $accountId = UserService::getCurrentAccountWildberriesId();
        return $query->join('wb_product_user AS wb_product_user1', 'wb_product_user1.imt_id', '=', 'wb_products.imt_id')->where([
            'wb_product_user1.account_id' => $accountId,
        ])->whereNull('wb_product_user1.deleted_at');
    }

    /**
     * Get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        $userId = UserService::getUserId();
        return $query->join('wb_product_user AS wb_product_user2', 'wb_product_user2.imt_id', '=', 'wb_products.imt_id')->where([
            'wb_product_user2.user_id' => $userId,
        ])->whereNull('wb_product_user2.deleted_at');
    }

    /**
     * Список подбора для товара.
     *
     * @return HasMany
     */
    public function pickListProducts(): HasMany
    {
        return $this->hasMany(WbPickListProduct::class, 'wb_product_id');
    }

    /**
     * Get history positions for product
     *
     * @return HasMany
     */
    public function getOptimisationHistory(): HasMany
    {
        return $this->hasMany(OptimisationHistory::class, 'product_id');
    }

    /**
     * Escrow hash
     *
     * @return HasMany
     */
    public function escrowHash(): HasMany
    {
        return $this->hasMany(EscrowHash::class, 'product_id', 'id')->select('name', 'image_hash', 'nmid');
    }

    /**
     * Escrow certificate
     *
     * @return HasMany
     */
    public function certificate(): HasMany
    {
        return $this->hasMany(EscrowCertificate::class, 'product_id', 'id')->select('link', 'lang', 'full_name', 'copyright_holder', 'email', 'nmid', 'created_at');
    }


    /**
     * Get products of current user
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccount($query)
    {
        $userId = UserService::getUserId();
        $accountId = UserService::getAccountId();
        return $query->join('wb_product_user', 'wb_product_user.imt_id', '=', 'wb_products.imt_id')->where([
            'wb_product_user.user_id' => $userId,
            'wb_product_user.account_id' => $accountId
        ])->whereNull('wb_product_user.deleted_at');
    }


    /**
     * Get products of current user
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccountByParams($query, int $userId, int $accountId)
    {
        return $query->join('wb_product_user', 'wb_product_user.imt_id', '=', 'wb_products.imt_id')->where([
            'wb_product_user.user_id' => $userId,
            'wb_product_user.account_id' => $accountId
        ])->whereNull('wb_product_user.deleted_at');
    }


    /**
     * Get products of current user with not active
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccountWithNotActive($query)
    {
        $userId = UserService::getUserId();
        $accountId = UserService::getAccountId();
        return $query->join('wb_product_user', 'wb_product_user.imt_id', '=', 'wb_products.imt_id')->where([
            'wb_product_user.user_id' => $userId,
            'wb_product_user.account_id' => $accountId,
        ]);
    }

    /**
     * Relations for user and accounts for model from table wb_product_user for many products
     */
    public function usersAndAccounts()
    {
        return $this->hasMany(WbProductUser::class, 'imt_id', 'imt_id')
            ->whereNull('wb_product_user.deleted_at');
    }


    /**
     * Relations for user and accounts for model from table wb_product_user for one product
     */
    public function userAndAccount($userId, $accountId)
    {
        return $this->hasMany(WbProductUser::class, 'imt_id', 'imt_id')
            ->where([
                'wb_product_user.user_id' => $userId,
                'wb_product_user.account_id' => $accountId,
            ])->whereNull('wb_product_user.deleted_at');
    }

    /**
     * Attach user and account to wb_products from table wb_product_user
     * @param $userId
     * @param $accountId
     * @throws \Exception
     */
    public function attachUserAndAccount($userId = 0, $accountId = 0): bool
    {
        if (!$userId || !$accountId) {
            throw new \Exception('Не заданы userId или accountId');
            die();
        }
        $attachedButNotActive = DB::table('wb_product_user')->select()->where([
            'user_id' => $userId,
            'account_id' => $accountId,
            'imt_id' => $this->imt_id,
        ])->whereNotNull('deleted_at')->first();
        if ($attachedButNotActive) {
            DB::table('wb_product_user')->where([
                'user_id' => $userId,
                'account_id' => $accountId,
                'imt_id' => $this->imt_id
            ])->whereNotNull('deleted_at')->update(['deleted_at' => null]);
        } else {
            DB::table('wb_product_user')->insert([
                'user_id' => $userId,
                'account_id' => $accountId,
                'imt_id' => $this->imt_id
            ]);
        }
        return true;
    }


    /**
     * Detach user and account to wb_products from table wb_product_user
     * @param $userId
     * @param $accountId
     * @throws \Exception
     */
    public function detachUserAndAccount($userId = 0, $accountId = 0): bool
    {
        if (!$userId || !$accountId) {
            throw new \Exception('Не заданы userId или accountId');
            die();
        }
        $attachedAndActive = DB::table('wb_product_user')->select()->where([
            'user_id' => $userId,
            'account_id' => $accountId,
            'imt_id' => $this->imt_id
        ])->whereNull('deleted_at')->first();
        if ($attachedAndActive) {
            DB::table('wb_product_user')->where([
                'user_id' => $userId,
                'account_id' => $accountId,
                'imt_id' => $this->imt_id,
            ])->whereNull('deleted_at')->update(['deleted_at' => \Carbon\Carbon::now()]);
        }
        return true;
    }

    /**
     * Escrow history
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(EscrowHistory::class, 'product_id', 'id')->select([
            'id',
            'account_id',
            'product_id',
            'nmid',
            'copyright_holder',
            'full_name',
            'email',
            'created_at',
            'updated_at'
        ])->where('deleted_at', null);
    }

    /**
     * @return HasOne
     */
    public function directory(): HasOne
    {
        return $this->hasOne(WbDirectoryItem::class, 'title', 'parent');
    }
}
