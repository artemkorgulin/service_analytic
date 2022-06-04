<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Models\OzProductStatus;
use App\Services\UserService;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceUpdater;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class UpdateProductStatus
 * По какой то причине некоторые товары остаются в статусе на модерации.
 * Эта команда нужна для ручного обновления их статусов
 * @package App\Console\Commands
 */
class UpdateProductStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:products:update-status
        {--status= : Статус проверяемых продуктов verified / moderation / error / created если его нет проверяем все }';

    protected $statuses = [
        'verified' => 1, 'moderation' => 2, 'error' => 3, 'created' => 4, 'failed_validation' => 5,
    ];

    /**
     * Url для получения информации по пользователям
     *
     * @var string
     */
    protected $accountsUrl = '/v1/get-all-vp-accounts/1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для ручного обновления статусов товара из озона, которые находятся на модерации';

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
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $productStatus = null;

        if ($this->option('status'))
            $productStatus = OzProductStatus::firstWhere('code', $this->option('status'));

        $url = env('WEB_APP_API_URL', '') . $this->accountsUrl;

        $token = UserService::auth();

        $accounts = Http::withToken($token)->get($url)->json();

        foreach ($accounts as $account) {
            $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
            if (is_array($ozonApiClient) && isset($ozonApiClient['statusCode']) && isset($ozonApiClient['error'])) {
                // todo сделать информирование о сбросе аккаунта
                Log::error($ozonApiClient['error'], __('Ozon API'));
                continue;
            }
            $productsQuery = OzProduct::where(['account_id' => $account['id']]);

            if ($productStatus) {
                $productsQuery = $productsQuery->where(['status_id' => $productStatus->id]);
            }

            $productsQuery->chunk(20, function ($products) use ($account, $productStatus, $ozonApiClient) {
                    $skuList = [];
                    foreach ($products as $product) {
                        $skuList[] = (int) $product->sku;
                    }

                    $this->info(__('Make request for products with skus: ').join(', ',$skuList));

                    $req = [
                        'sku' => $skuList,
                    ];

                    $productsInfoResponse = $ozonApiClient->repeat('getProductInfoList', $req);

                    if ($productsInfoResponse['statusCode'] !== 200) {
                        $this->error('Invalid ozon response' . json_encode($productsInfoResponse));
                        Log::error(json_encode($productsInfoResponse));
                        return;
                    }

                    if(isset($productsInfoResponse['data']['result']['items'])){
                        $items = $productsInfoResponse['data']['result']['items'];
                        foreach ($products as $product) {
                            foreach ($items as $item) {
                                if ($product->offer_id === $item['offer_id'] && (isset($productStatus->code)) && $item['state'] != $productStatus->code) {
                                    $productService = new ProductServiceUpdater($product);
                                    $productService->updateStatus($item['state']);
                                    $this->info(__("Ozon product :productId status updated: :state",
                                        ['productId' => $product->sku, 'state' => $item['state']]));
                                } elseif ($product->offer_id === $item['offer_id'] && isset($this->statuses[$item['state']])
                                    && $product->status_id != $this->statuses[$item['state']]) {
                                    $productService = new ProductServiceUpdater($product);
                                    $productService->updateStatus($item['state']);
                                    $this->info(__("Ozon product :productId status updated: :state",
                                        ['productId' => $product->sku, 'state' => $item['state']]));
                                }
                            }
                        }
                    }
                });
        }

        return 0;
    }
}
