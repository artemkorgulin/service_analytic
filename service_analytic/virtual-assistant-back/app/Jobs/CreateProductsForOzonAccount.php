<?php

namespace App\Jobs;

use App\Classes\Helper;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Services\HelperWABService;
use App\Services\V2\OzonApi;
use App\Services\V2\OzonStocksService;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CreateProductsForOzonAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $accountId;
    protected $accountClientId;
    protected $accountApiKey;
    protected $user;
    protected int $itemsPerPage = 1000;
    protected int $chunkSize = 100;
    protected int $maxErrorCount = 3;

    /**
     * Create a new job instance.
     *
     * @param array $user
     * @param array $accountParams
     */
    public function __construct(array $user, array $accountParams)
    {
        if (empty($user) || empty($user['id']) || empty($accountParams['id']) || empty($accountParams['client_id']) || empty($accountParams['client_api_key'])) {
            $this->fail((new Exception('Недостаточно данных для выполнения задания. Пользователь или аккаунт не переданы.')));
        }

        $this->user = $user;
        $this->accountId = $accountParams['id'];
        $this->accountClientId = $accountParams['client_id'];
        $this->accountApiKey = $accountParams['client_api_key'];
    }

    /**
     * Execute the job.
     *
     * @param OzonStocksService $ozonStocksService
     * @return void
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    public function handle(OzonStocksService $ozonStocksService)
    {
        // check existing products
        if (!empty($this->countProducts())) {
            Artisan::call('ozon:update-products-for-accounts', ['--account_id' => $this->accountId]);
            return;
        }

        // check account
        $account = HelperWABService::getAccount($this->accountId);
        if (empty($account['platform_client_id'])) {
            Log::channel('queues')->error('Not set platform_client_id for account id: ' . $this->accountId);
            return;
        }

        // log and notify about start
        $this->beforeUpload();

        // get products from OZON
        $ozonApi = new OzonApi($this->accountClientId, $this->accountApiKey);
        $items = [];
        $page = 0;
        $errors = 0;
        do {
            $response = $ozonApi->repeat('getProductList', [
                'page' => $page,
                'page_size' => $this->itemsPerPage,
            ]);
            $page++;

            if (!isset($response['data']['result']['items'])) {
                Log::channel('queues')->alert('OZON RESPONSE: ' . print_r($response, true));
                $errors++;
                if ($errors > $this->maxErrorCount) {
                    break;
                } else {
                    continue;
                }
            }

            $c = count($response['data']['result']['items']);

            print ("Страница $page вернула $c товаров с Ozon. \n");
            Log::channel('queues')->info('Страница ' . $page . ' вернула ' . $c . ' товаров с Ozon. Аккаунт ' . $this->accountClientId);
            $external_ids = array_map(function ($item) {
                return $item['product_id'];
            }, $response['data']['result']['items']);
            array_map(function ($item) use (&$items) {
                $items[] = $item;
            }, $external_ids);
        } while (count($response['data']['result']['items']) > 0);

        // save products to DB
        Log::channel('queues')->info('Сохранение товаров с Ozon в базу данных. Аккаунт ' . $this->accountClientId);
        foreach (array_chunk($items, $this->chunkSize) as $productIds) {
            $req = ['product_id' => $productIds];
            $productsInfoResponse = $ozonApi->repeat('getProductInfoList', $req);

            $externalIdsForDelete = [];
            $insertProducts = [];
            if ($productsInfoResponse['statusCode'] === 200) {
                foreach ($productsInfoResponse['data']['result']['items'] as $productInfo) {
                    $productCategory = OzCategory::where(['external_id' => $productInfo['category_id']])->first();
                    $skuFbo = $productInfo['fbo_sku'] ?? null;
                    $skuFbs = $productInfo['fbs_sku'] ?? null;
                    if (!empty($skuFbo) || !empty($skuFbs)) {
                        $externalIdsForDelete[] = (int)$productInfo['id'];
                        $productFeaturesResponse = $ozonApi->ozonRepeat('getProductFeatures',
                            [$productInfo['offer_id']]);
                        if (!isset($productFeaturesResponse['data']['result'])) {
                            continue;
                        }
                        $productFeatures = reset($productFeaturesResponse['data']['result']);
                        $insertProducts[] = [
                            'user_id' => $this->user['id'],
                            'account_client_id' => $account['platform_client_id'],
                            'account_id' => $this->accountId,
                            'external_id' => $productInfo['id'],
                            'title' => $productInfo['name'],
                            'sku_fbo' => $skuFbo,
                            'sku_fbs' => $skuFbs,
                            'brand' => trim(Helper::ozCardGetBrand($productFeatures)),
                            'barcode' => $productInfo['barcode'],
                            'offer_id' => $productInfo['offer_id'],
                            'category_id' => $productCategory->id ?? $productInfo['category_id'],
                            'category' => $productCategory->name ?? null,
                            'image' => $productInfo['images']['0'] ?? ($productInfo['primary_image'] ?? null),
                            'images' => json_encode($productInfo['images']),
                            'vat' => $productInfo['vat'],
                            'price' => $productInfo['marketing_price'] ?: 0,
                            'min_ozon_price' => $productInfo['min_ozon_price'] ?: 0,
                            'buybox_price' => $productInfo['buybox_price'] ?: 0,
                            'premium_price' => $productInfo['premium_price'] ?: 0,
                            'recommended_price' => $productInfo['recommended_price'] ?: 0,
                            'old_price' => $productInfo['old_price'] ?: 0,
                            'data' => json_encode($productFeatures),
                            'updated_at' => Carbon::now(),
                            'created_at' => Carbon::now(),
                            'deleted_at' => null,
                        ];
                    }
                }
                try {
                    OzTemporaryProduct::whereIn('external_id', $externalIdsForDelete)->forceDelete();
                    OzTemporaryProduct::insert($insertProducts);
                } catch (\Exception $exception) {
                    report($exception);
                    Log::channel('queues')->error($exception->getMessage() . ' Аккаунт ' . $this->accountClientId);
                }
            }
        }
        Log::channel('queues')->info('Завершено сохранение товаров с Ozon в базу данных. Аккаунт ' . $this->accountClientId);

        // log and notify about finish and update stocks
        $this->afterUpload($ozonStocksService);
    }

    /**
     * Count account products
     *
     * @return int
     */
    private function countProducts(): int
    {
        return $this->countProductsForSku('sku_fbo');
    }

    /**
     * Count products for current SKU field (sku_fbo / sku_fbs)
     *
     * @param $skuField
     * @return int
     */
    private function countProductsForSku($skuField): int
    {
        $query = OzTemporaryProduct::select($skuField)
            ->distinct()
            ->where('account_id', '=', $this->accountId);
        $getUsedSku = OzProduct::select($skuField)
            ->distinct()
            ->where('user_id', '=', $this->user['id'])
            ->where('account_id', '=', $this->accountId)
            ->get();
        if (!$getUsedSku->isEmpty()) {
            $query = $query->whereNotIn(
                $skuField,
                $getUsedSku->pluck($skuField)->all()
            );
        }
        return $query->count();
    }

    /**
     * Log and notify before upload products
     *
     * @return void
     */
    private function beforeUpload(): void
    {
        Log::channel('queues')->info('Начало загрузки товаров с Ozon. Аккаунт ' . $this->accountClientId);
        try {
            UsersNotification::dispatch(
                'marketplace.account_product_upload_start',
                [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                ['marketplace' => 'Ozon']
            );
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
    }

    /**
     * Log, notify and update stocks after upload products
     *
     * @param OzonStocksService $ozonStocksService
     * @return void
     */
    private function afterUpload(OzonStocksService $ozonStocksService): void
    {
        $counted = $this->countProducts();
        try {
            if (!empty($counted)) {
                UsersNotification::dispatch(
                    'marketplace.account_product_upload_success',
                    [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                    ['counted' => $counted, 'marketplace' => 'Ozon']
                );
                $ozonStocksService->updateStocks($this->accountId, $this->accountClientId, $this->accountApiKey);
            } else {
                UsersNotification::dispatch(
                    'marketplace.account_product_upload_fail',
                    [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                    ['marketplace' => 'Ozon']
                );
            }
        } catch (\Exception $exception) {
            report($exception);
            Log::channel('queues')->error($exception->getMessage());
        }
        Log::channel('queues')->info('Окончание загрузки товаров с Ozon. Аккаунт ' . $this->accountClientId);
    }
}
