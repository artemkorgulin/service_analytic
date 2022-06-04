<?php


namespace App\Services\V2;


use App\Classes\Helper;
use App\Constants\Errors\ProductsErrors;
use App\Constants\OzonConstants;
use App\Constants\References\ProductStatusesConstants;
use App\Exceptions\Ozon\OzonApiException;
use App\Exceptions\Ozon\OzonServerException;
use App\Exceptions\Product\ProductException;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\OzProductStatus;
use App\Models\OzTemporaryProduct;
use App\Services\InnerService;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Сервис для добавления товаров в отслеживаемые
 * Class ProductTrackingService
 * @package App\Services\V2
 */
class ProductTrackingService
{
    /**
     * Пользователь
     * @var User
     */
    private $user;

    /**
     * Его активный account
     * @var
     */
    private $account;


    /**
     * Class constructor
     * @param int $userId
     * @param int $accountId
     */
    public function __construct(int $userId = 0, int $accountId = 0)
    {
        if ($userId && $accountId) {
            $innerService = new InnerService();
            $this->account = $innerService->getAccount($accountId) ?? null;
            $this->user = $innerService->getUser($userId) ?? null;
        }
    }

    /**
     * Добавить временный (пустой) товар
     *
     * @param string $skuFbo
     * @throws ProductException
     */
    private function addTempProduct(string $skuFbo)
    {
        if (!$this->account) {
            $this->account = UserService::getCurrentAccount();
        }
        $savedProducts = OzProduct::where(['sku_fbo' => $skuFbo])->get();
        if ($savedProducts->where('account_id', '!=', $this->account['id'])->count()) {
            throw new ProductException(ProductsErrors::ALREADY_ADDED_BY_ANOTHER_USER);
        } elseif ($savedProducts->count() || OzTemporaryProduct::currentUser()->where('sku_fbo', '=', $skuFbo)->exists()) {
            throw new ProductException(ProductsErrors::ALREADY_EXISTS);
        }

        $tempProduct = new OzTemporaryProduct;
        $tempProduct->sku_fbo = $skuFbo;
        $tempProduct->user_id = $this->account['pivot']['user_id'];
        $tempProduct->account_id = $this->account['id'];
        $tempProduct->save();
        throw new ProductException(ProductsErrors::ADDED_WITHOUT_DATA);
    }

    /**
     * Добавить СКУ в отслеживаемые
     *
     * @param string $skuFbo
     * @return OzProduct
     * @throws OzonServerException
     * @throws ProductException
     */
    public function trackProduct(string $skuFbo): OzProduct
    {
        if (!$this->account && !$this->user) {
            $this->account = UserService::getCurrentAccount();
            $this->user = UserService::getUser();
        }
        $ozon = new OzonApi($this->account['platform_client_id'], $this->account['platform_api_key']);
        $url = OzonConstants::PRODUCT_DETAIL_URL . $skuFbo;
        $savedProducts = OzProduct::where(['sku_fbo' => $skuFbo])->get();
//        todo добавить lock иначе можно добавить товары с одинаковым SKU
        if ($savedProducts->where('account_id', '!=', $this->account['id'])->count()) {
            throw new ProductException(ProductsErrors::ALREADY_ADDED_BY_ANOTHER_USER);
        } elseif ($savedProducts->count()) {
            throw new ProductException(ProductsErrors::ALREADY_EXISTS);
        }
        $product_info_response = $ozon->ozonRepeat('getProductInfo', $skuFbo);
        if (!isset($product_info_response['data']['result'])) {
            throw new ProductException(ProductsErrors::NOT_FOUND_IN_OZON);
        }
        $product_info = $product_info_response['data']['result'];

        $productFeaturesResponse = $ozon->ozonRepeat('getProductFeatures', [$product_info['offer_id']]);
        if (empty($productFeaturesResponse['data']['result'])) {
            throw new ProductException(ProductsErrors::EMPTY_RESULT_IN_RESPONSE);
        }
        $productFeatures = reset($productFeaturesResponse['data']['result']);

        if (!$product_info['visible']) {
            throw new ProductException(ProductsErrors::NOT_FOR_SALE);
        }
        /** @var OzCategory $product_category */
        $product_category = OzCategory::where(['external_id' => $product_info['category_id']])->first();

        $product = OzProduct::withTrashed()->firstWhere([
            'sku_fbo' => $skuFbo,
            'external_id' => $product_info['id'],
            'offer_id' => $product_info['offer_id'],
            'account_id' => $this->account['id']
        ]);

        if ($product) {
            $product->restore();
        } else {
            $product = new OzProduct;
        }

        $images = array_unique(array_merge([$product_info['primary_image']], $product_info['images']));

        $product->external_id = $product_info['id'];
        $product->name = $product_info['name'];
        $product->offer_id = $product_info['offer_id'];
        $product->brand = Helper::ozCardGetBrand($productFeatures);
        $product->category_id = $product_category->id;
        $product->price = $product_info['marketing_price'] ?: 0;
        $product->old_price = $product_info['old_price'] ?: 0;
        $product->photo_url = $product_info['primary_image'] ?? $images[0] ?? null;
        $product->images = $images ?? [];
        $product->sku_fbo = $skuFbo;
        $product->sku_fbs = $product_info['fbs_sku'] ?? null;
        $product->url = $url;
        $product->user_id = $this->user['id'] ?? 0;
        $product->account_id = $this->account['id'];

        /** @var OzProductStatus $status */
        $status = OzProductStatus::query()->where('code', ProductStatusesConstants::VERIFIED_CODE)->first();
        $product->status_id = $status->id;
        $product->save();

        $productServiceUpdater = new ProductServiceUpdater($product->id);
        $productServiceUpdater->updateWeightsAndDimensions();
        $productServiceUpdater->updateFeatures();
        $productServiceUpdater->updateStats();

        return $product;
    }

    /**
     * Перенос временных товаров в отслеживаемые
     * @param string|null $searchFilter
     * @return array
     */
    public function loadTempProducts(?string $searchFilter = ''): array
    {
        $tempProducts = OzTemporaryProduct::currentUser()->searchBySkuFbo($searchFilter)->get();
        $productsTracked = false;
        $loadedProducts = [];
        Cache::lock('temp_product_sync', 60)->get(function () use ($tempProducts, &$loadedProducts, &$productsTracked) {
            ModelHelper::transaction(function () use ($tempProducts, &$loadedProducts, &$productsTracked) {
                foreach ($tempProducts as $key => $tempProduct) {
                    $loadedProducts[$key]['sku'] = $tempProduct->sku_fbo;
                    try {
                        self::trackProduct($tempProduct->sku_fbo);
                        $loadedProducts[$key]['code'] = 200;
                        $loadedProducts[$key]['message'] = 'Успешно загружен';
                        $productsTracked = true;
                        $tempProduct->delete();
                    } catch (OzonApiException | ProductException $exception) {
                        report($exception);
                        $loadedProducts[$key]['code'] = $exception->getCode();
                        $loadedProducts[$key]['message'] = $exception->getMessage();
                        $tempProduct->delete();
                        continue;
                    }
                }
            });
        });

        if ($productsTracked) {
            try {
                (new FtpService())->sendSkuRequestFile();
            } catch (Exception $exception) {
                report($exception);
                ExceptionHandlerHelper::logFail($exception);
            }
        }
        return $loadedProducts;
    }
}
