<?php

namespace App\Console\Commands;

use App\Constants\Errors\ProductsErrors;
use App\Constants\References\ProductStatusesConstants;
use App\Exceptions\Product\ProductException;
use App\Models\OzCategory;
use App\Models\OzProduct;
use App\Models\OzProductStatus;
use App\Services\InnerService;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceUpdater;
use App\Services\V2\ProductTrackingService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class OzonSyncProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:sync
                            {--id= : Id товара Ozon для перезагрузки (наше)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация перезагрузка продукта Ozon';

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
        // Perform exception
        $id = $this->option('id');
        if (!$id) {
            $this->error('Нет id товара для загрузки! Прерываю процесс!');
            return Command::FAILURE;
        }

        $product = OzProduct::findOrFail($id);
        if (!$product) {
            $this->error('Нет товара для загрузки! Прерываю процесс!');
            return Command::FAILURE;
        }

        $account = (new InnerService)->getAccount($product->account_id);
        if (!$account) {
            $this->error('Не удалось получить данные аккаунта! Прерываю процесс!');
            return Command::FAILURE;
        }

        $ozonApi = new OzonApi($account['platform_client_id'], $account['platform_api_key']);

        $product_info_response = $ozonApi->ozonRepeat('getProductInfo', $product->sku_fbo);
        if (empty($product_info_response['data']['result'])) {
            $this->error('Не удалось получить данные товара с Ozon! Прерываю процесс!');
            return Command::FAILURE;
        }

        $product_info = $product_info_response['data']['result'];
        if (!$product_info['visible']) {
            throw new ProductException(ProductsErrors::NOT_FOR_SALE);
        }

        /**
         * @var OzCategory $product_category
         */
        $product_category = OzCategory::where(['external_id' => $product_info['category_id']])->first();
        $product->external_id = $product_info['id'];
        $product->sku_fbo = $product_info['fbo_sku'] ?? null;
        $product->sku_fbs = $product_info['fbs_sku'] ?? null;
        $product->name = $product_info['name'];
        $product->offer_id = $product_info['offer_id'];
        $product->category_id = $product_category->id;
        $product->price = $product_info['marketing_price'] ?: 0;
        $product->old_price = $product_info['old_price'] ?: 0;
        $product->photo_url = $product_info['images']['0'] ?? '';

        /** @var OzProductStatus $status */
        $status = OzProductStatus::query()->where('code', ProductStatusesConstants::VERIFIED_CODE)->first();
        $product->status_id = $status->id;
        $product->save();

        $productServiceUpdater = new ProductServiceUpdater($product->id);

        $productServiceUpdater->updateWeightsAndDimensions();
        $productServiceUpdater->updateFeatures();
        $productServiceUpdater->updateStats();

        return Command::SUCCESS;
    }
}
