<?php

namespace App\Console\Commands;

use App\Classes\Helper;
use App\Models\OzCategory;
use App\Models\OzTemporaryProduct;
use App\Services\InnerService;
use App\Services\V2\OzonApi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOzonProductForAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:update-products-for-accounts  {--account_id= : Номер аккаунта, товары для которого необходимо перегрузить }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление товаров для аккаунтов Ozon';

    /** @var int platform id */
    protected $platformId = 1;

    /** @var int products in page for one request */
    protected $itemsPerPage = 990;

    /** @var int chunk size for insert or create Ozon accounts */
    protected $chunkSize = 49; // вот это нужно иметь ввиду запросить товаров хоть 10000 а вот характеристик по товарам
    // не долее 99

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $accountId = $this->option('account_id');

        if ($accountId) {
            $accounts = $this->getCertainAccount($accountId);
        } else {
            $accounts = $this->getAllAccounts();
        }

        if (!$accounts) {
            return Command::FAILURE;
        }
        foreach ($accounts as $account) {
            $this->updateTemporaryProductAccount($account);
        }
        return Command::SUCCESS;
    }

    /**
     * Get all accounts for platform
     * @return array|mixed
     */
    private function getAllAccounts()
    {
        return (new InnerService())->getAllSellerAccounts($this->platformId);
    }

    /**
     * Get certain account
     * @return array|mixed
     */
    private function getCertainAccount($accountId)
    {
        try {
            return [(new InnerService())->getAccount($accountId)];
        } catch (\Exception $exception) {
            report($exception);
            return [];
        }
    }

    /**
     * Update temporary products for accounts
     * @param array $account
     * @return bool
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    private function updateTemporaryProductAccount(array $account): bool
    {
        if (!$account || !$account['platform_client_id'] || !$account['platform_api_key']) {
            return false;
        }
        $ozonApi = new OzonApi($account['platform_client_id'], $account['platform_api_key']);

        $externalIds = $this->getExternalIds($ozonApi, $account);

        if (!$externalIds) {
            return false;
        }

        $temporaryExternalIds = OzTemporaryProduct::withTrashed()->select(['external_id'])->where('account_id', $account['id'])
            ->pluck('external_id')->toArray();


        // Not use collection here - memory leak is possible
        $diff1 = array_diff($externalIds, $temporaryExternalIds);
        $diff2 = array_diff($temporaryExternalIds, $externalIds);
        $countedUpdated = 0;
        $countedCreated = 0;

        $this->info(__('For Ozon account :account I must create :create, delete :delete and update :update products.',
            ['account' => $account['platform_client_id'], 'create' => count($diff1), 'delete' => count($diff2),
                'update' => count($externalIds) - count($diff1)]));

        //Delete old products from temporary (safe delete)
        OzTemporaryProduct::where('account_id', $account['id'])->whereIn('external_id', $diff2)->delete();

        foreach (array_chunk($externalIds, $this->chunkSize) as $productIds) {
            $req = ['product_id' => array_values($productIds)];

            $productsInfoResponse = $ozonApi->repeat('getProductInfoList', $req);
            if ($productsInfoResponse['statusCode'] !== 200
                || empty($productsInfoResponse['data']['result']['items'])) {
                continue;
            }

            $productIds = [];
            foreach ($productsInfoResponse['data']['result']['items'] as $productInfo) {
                $productIds[] = $productInfo['id'];
            }

            $this->info("Make request for Ozon - use method https://api-seller.ozon.ru/v2/products/info/attributes");

            $productFeaturesResponse = $ozonApi->ozonRepeat(
                'getProductFeaturesByProductId',
                $productIds
            );
            if (!isset($productFeaturesResponse['statusCode']) || $productFeaturesResponse['statusCode'] !== 200
                || empty($productFeaturesResponse['data']['result'])) {
                continue;
            }
            foreach ($productFeaturesResponse['data']['result'] as $productFeatures) {
                $productInfo = $this->getProductFromListByExternalId(
                    $productsInfoResponse['data']['result']['items'],
                    $productFeatures['id']
                );

                if (!$productInfo) {
                    continue;
                }

                $productCategory = OzCategory::where(['external_id' => $productInfo['category_id']])->first();

                $skuFbo = $productInfo['fbo_sku'] ?? null;
                $skuFbs = $productInfo['fbs_sku'] ?? null;
                if (empty($skuFbo) && empty($skuFbs)) {
                    continue;
                }

                $currentDateTime = Carbon::now()->toDateTimeString();
                try {
                    $insertData = [
                        'account_id' => $account['id'],
                        'account_client_id' => $account['platform_client_id'],
                        'external_id' => $productInfo['id'],
                        'user_id' => $account['user_id'] ?? 0,
                        'title' => $productInfo['name'],
                        'sku_fbo' => $skuFbo,
                        'sku_fbs' => $skuFbs,
                        'brand' => Helper::ozCardGetBrand($productFeatures),
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
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ];

                    $record = DB::table('oz_temporary_products')->where([
                        'external_id' => $productInfo['id'],
                    ])->first();

                    if ($record) {
                        // Вот это фигнёй как "!==", лучше не заниматься потому что у нас цены и ID хранятся как числа у
                        // маркетплейсов все возвращается как "2313.00" или "34342423434" в общем как строки.
                        // Особенно касаемо Wildberries приводить одно в другое, как то неразумно сравнивать != или ==
                        if (
                            $record->title != $productInfo['name'] ||
                            $record->sku_fbo != $skuFbo ||
                            $record->sku_fbs != $skuFbs ||
                            $record->price != $productInfo['marketing_price'] ?: 0 ||
                            $record->min_ozon_price != $productInfo['min_ozon_price'] ?: 0 ||
                            $record->buybox_price != $productInfo['buybox_price'] ?: 0 ||
                            $record->premium_price != $productInfo['premium_price'] ?: 0 ||
                            $record->recommended_price != $productInfo['recommended_price'] ?: 0 ||
                            $record->old_price != $productInfo['old_price'] ?: 0
                        ) {
                            unset($insertData['created_at']);
                            DB::table('oz_temporary_products')->where([
                                'account_id' => $account['id'],
                                'external_id' => $productInfo['id'],
                            ])->update($insertData);
                            $this->info(__('Update Ozon product SKU (FBO :skuFbo, FBS :skuFbs) - product ":name".', [
                                'skuFbo' => $skuFbo,
                                'skuFbs' => $skuFbs,
                                'name' => $productInfo['name']
                            ]));
                            $countedUpdated++;
                        }
                    } else {
                        DB::table('oz_temporary_products')->insert($insertData);
                        $this->info(__('Create Ozon product SKU (FBO :skuFbo, FBS :skuFbs) - product ":name".', [
                            'skuFbo' => $skuFbo,
                            'skuFbs' => $skuFbs,
                            'name' => $productInfo['name']
                        ]));
                        $countedCreated++;
                    }
                } catch (\Exception $exception) {
                    report($exception);
                    $this->error($exception->getMessage() . ' account ' . $account['platform_client_id']);
                }
            }
        }

        $this->info(__('Created :created and updated :updated products in Ozon for account :account.', [
            'created' => $countedCreated,
            'updated' => $countedUpdated,
            'account' => $account['platform_client_id']
        ]));
        return true;
    }

    /**
     * Get external (Ozon) product ids
     * @param $ozonApi
     * @return array|false
     */
    private function getExternalIds($ozonApi, $account): bool|array
    {
        $page = 1;
        $externalIds = [];
        do {
            $response = $ozonApi->repeat('getProductList', [
                'page' => $page,
                'page_size' => $this->itemsPerPage,
            ]);
            if (!isset($response['data']['result']['items'])) {
                $this->alert('Ozon response: ' . print_r($response, true));
                return false;
            }
            $count = count($response['data']['result']['items']);
            $this->info(__("Page :page return :count Ozon products.", ['page' => $page, 'count' => $count]));
            $externalIds = array_merge($externalIds, array_map(function ($item) {
                return $item['product_id'];
            }, $response['data']['result']['items']));

            $page++;
        } while (count($response['data']['result']['items']) > 0);

        return $externalIds;
    }

    /**
     * Get product from product list (Ozon)
     * @param array $productList
     * @param int $id
     * @return mixed
     */
    private function getProductFromListByExternalId(array $productList, int $id): mixed
    {
        foreach ($productList as $product) {
            if ((int)$product['id'] === $id) {
                return $product;
            }
        }
        return false;
    }
}
