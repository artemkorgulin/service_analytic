<?php

namespace App\Console\Commands;

use App\Classes\Helper;
use App\Models\JobNewAccount;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use App\Services\UserService;
use App\Services\V2\OzonApi;
use App\Services\Wildberries\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Предварительная загрузка товаров в аккаунт Wildberries или Ozon';

    /**
     * Аккаунт
     * @var
     */
    protected $account;

    /**
     * Загрузка товаров за одну итерацию
     * @var int
     */
    protected $itemsPerPage = 250;

    /**
     * Размер чанка
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * Create a new command instance.
     *
     * @return void
     * @throws \Exception
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
        $performAccount = JobNewAccount::whereNull('command_start_at')->first();
        if ($performAccount) {
            // Get account and save jobs_new_account
            $id = $performAccount->account_id;
            $performAccount->command_start_at = Carbon::now()->toDateTimeString();
            $performAccount->save();
            $this->account = UserService::loadAccount($id);
            $platform = $this->account['platform_title'];
            // запускаем команду на вставку
            if ($platform) {
                $func = 'handle' . $platform;
                $this->{$func}();
            }
        }
    }

    /**
     * Команда по для получения предзагруженных товаров из Wildberries
     * @throws \Exception
     */
    protected function handleWildberries()
    {
        if (isset($this->account['platform_client_id'])
            && isset($this->account['platform_api_key'])) {

            $wbApi = new Client($this->account['platform_client_id'], $this->account['platform_api_key']);

            // initially upload wb nomenclatures
            $nomenclatures = collect($wbApi->getInfo());

            foreach ($nomenclatures->chunk($this->chunkSize) as $nomenclaturesPart) {
                $records = [];
                foreach ($nomenclaturesPart as $nomenclature) {
                    if (!in_array($nomenclature->nmId, array_column($records, 'nm_id'))) {
                        $records[] = [
                            'user_id' => $this->account['user_id'] ?? null,
                            'account_id' => $this->account['id'],
                            'nm_id' => $nomenclature->nmId,
                            'price' => $nomenclature->price,
                            'discount' => $nomenclature->discount,
                            'promocode' => $nomenclature->promoCode,
                        ];
                    }

                }
                WbNomenclature::upsert($records, ['account_id', 'nm_id'], ['user_id', 'price', 'discount', 'promocode']);
            }

            // work with temporary Wildberries products
            $page = 0;

            $cards = collect($wbApi->getCardList($this->itemsPerPage, $page * $this->itemsPerPage));

            do {
                $records = [];
                foreach ($cards as $card) {

                    $card = json_decode(json_encode($card), true);

                    if (isset($card['id'])) {

                        if (WbProduct::firstWhere('card_id', $card['id'])) {
                            continue;
                        }

                        if (!in_array($card['id'], array_column($records, 'card_id'))) {

                            $nomenclatures = Helper::wbCardGetNmIds($card) ?? [];

                            WbTemporaryProduct::updateOrCreate(
                                [
                                    'account_id' => $this->account['id'],
                                    'card_id' => $card['id'],
                                    'imt_id' => $card['imtId'],
                                ],
                                [
                                    'user_id' => $this->account['user_id'] ?? null,
                                    'card_user_id' => $card['userId'],
                                    'supplier_id' => $card['supplierId'],
                                    'imt_supplier_id' => $card['imtSupplierId'],
                                    'title' => Helper::wbCardGetTitle($card),
                                    'brand' => Helper::wbCardGetBrand($card),
                                    'barcodes' => json_encode(Helper::wbCardGetBarcodes($card)),
                                    'nmid' => $nomenclatures[0] ?? null,
                                    'sku' => $nomenclatures[0] ?? null,
                                    'image' => Helper::wbCardGetPhoto($card),
                                    'price' => Helper::wbCardGetPrice($card),
                                    'object' => $card['object'],
                                    'parent' => $card['parent'],
                                    'country_production' => $card['countryProduction'],
                                    'supplier_vendor_code' => $card['supplierVendorCode'],
                                    'data' => json_encode($card),
                                    'nomenclatures' => json_encode($nomenclatures),
                                    'url' => Helper::wbCardGetUrl($card),
                                ]);
                        }
                    }
                }


                $this->info("Работаю со страницей {$page}");

                $page++;

                $retry_count = 0;
                do {
                    try {
                        $cards = collect($wbApi->getCardList($this->itemsPerPage, $page * $this->itemsPerPage));
                    } catch (\GuzzleHttp\Exception\ConnectException $exception) {
                        // log the error here
                        report($exception);
                        sleep(30);
                        Log::warning('guzzle_connect_exception', [
                            'message' => Str::limit($exception->getMessage(), 500, '...')
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $exception) {
                        report($exception);
                        sleep(30);
                        Log::warning('guzzle_connection_timeout', [
                            'message' => Str::limit($exception->getMessage(), 500, '...')
                        ]);
                    } catch (\Exception $exception) {
                        report($exception);
                        sleep(30);
                        Log::warning('common_exception', [
                            'message' => Str::limit($exception->getMessage(), 500, '...')
                        ]);
                    }

                    // Do max 5 attempts
                    if (++$retry_count == 5) {
                        break;
                    }
                } while (!is_array($cards));
            } while (count($cards) > 0);
        }
    }

    /**
     * Команда по для получения предзагруженных товаров из Ozon
     * @throws \Exception
     */
    protected function handleOzon()
    {
        if (isset($this->account['platform_client_id'])
            && isset($this->account['platform_api_key'])) {

            $ozonApi = new OzonApi($this->account['platform_client_id'], $this->account['platform_api_key']);

            $items = [];

            $page = 0;

            do {

                $response = $ozonApi->repeat('getProductList', [
                    'page' => $page,
                    'page_size' => $this->itemsPerPage,
                ]);

                $page++;

                if (!isset($response['data']['result']['items'])) {
                    Log::alert('OZON RESPONSE: ' . print_r($response, true));
                }

                $c = count($response['data']['result']['items']);

                print ("Страница $page вернула $c товаров с Ozon. \n");

                $external_ids = array_map(function ($item) {
                    return $item['product_id'];
                }, $response['data']['result']['items']);

                $inOzProducts = OzProduct::select('external_id')->whereIn('external_id', $external_ids)->pluck('external_id')->toArray();

                // Я не хз просто вот почему-то это фигня на работает
                // $diff = array_diff($external_ids, $inOzProducts);
                array_map(function ($item) use ($inOzProducts, &$items) {
                    if (!in_array($item, $inOzProducts)) {
                        $items[] = $item;
                    }
                }, $external_ids);

            } while (count($response['data']['result']['items']) > 0);
            foreach (array_chunk($items, $this->chunkSize) as $product_ids) {
                $req = [
                    'product_id' => $product_ids,
                ];
                $productsInfoResponse = $ozonApi->repeat('getProductInfoList', $req);
                if ($productsInfoResponse['statusCode'] === 200) {
                    foreach ($productsInfoResponse['data']['result']['items'] as $product_info) {
                        if (OzProduct::where('external_id', $product_info['id'])->count()) {
                            continue;
                        }
                        /**
                         * @var OzCategory $product_category
                         */
                        $product_category = OzCategory::where(['external_id' => $product_info['category_id']])->first();

                        $skuFbo = $productInfo['fbo_sku'] ?? null;
                        $skuFbs = $productInfo['fbs_sku'] ?? null;
                        if (!empty($skuFbo) || !empty($skuFbs)) {
                            if (!OzProduct::firstWhere('external_id', $product_info['id'])) {

                                $productFeaturesResponse = $ozonApi->ozonRepeat('getProductFeatures',
                                    [$product_info['offer_id']]);
                                $productFeatures = reset($productFeaturesResponse['data']['result']);

                                OzTemporaryProduct::updateOrCreate(
                                    [
                                        'account_id' => $this->account['id'],
                                        'external_id' => $product_info['id'],
                                    ],
                                    [
                                        'user_id' => $this->account['user_id'],
                                        'title' => $product_info['name'],
                                        'sku_fbo' => $skuFbo,
                                        'sku_fbs' => $skuFbs,
                                        'brand' => Helper::ozCardGetBrand($productFeatures),
                                        'barcode' => $product_info['barcode'],
                                        'offer_id' => $product_info['offer_id'],
                                        'category_id' => $product_category->id ?? $product_info['category_id'],
                                        'category' => $product_category->name ?? null,
                                        'image' => $product_info['images']['0'] ?? ($product_info['primary_image'] ?? null),
                                        'images' => json_encode($product_info['images']),
                                        'vat' => $product_info['vat'],
                                        'price' => $product_info['marketing_price'] ?: 0,
                                        'min_ozon_price' => $product_info['min_ozon_price'] ?: 0,
                                        'buybox_price' => $product_info['buybox_price'] ?: 0,
                                        'premium_price' => $product_info['premium_price'] ?: 0,
                                        'recommended_price' => $product_info['recommended_price'] ?: 0,
                                        'old_price' => $product_info['old_price'] ?: 0,
                                        'data' => $productFeatures,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

    }
}
