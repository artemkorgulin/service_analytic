<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Services\InnerService;
use App\Services\V2\OzonApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OzonProductGetWightsAndDimensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:products-get-weights-and-dimensions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получаем все веса и размеры для продуктов Ozon';

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
     */
    public function handle()
    {

        foreach (OzProduct::select('account_id')->whereNotNull('account_id')->groupBy('account_id')->get() as $account) {
            $accountId = $account->account_id;
            $innerService = new InnerService();
            $account = $innerService->getAccount($accountId);
            if (!$account) {
                continue;
            }
            $ozonApi = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
            OzProduct::where('account_id', $accountId)->chunk(50, function ($chunk) use ($ozonApi) {
                $offerIds = [];
                foreach ($chunk as $product) {
                    $offerIds[] = $product->offer_id;
                }
                $response = $ozonApi->repeat('getProductFeatures', $offerIds);

                if (isset($response['statusCode']) && $response['statusCode'] === 200) {
                    $productsData = $response['data']['result'];

                    foreach ($productsData as $productData) {

                        try {
                            $product = OzProduct::firstWhere('offer_id', $productData['offer_id']);
                            $product->dimension_unit = $productData['dimension_unit'] ?? 'mm';
                            $product->weight_unit = $productData['weight_unit'] ?? 'g';
                            $product->depth = $productData['depth'] ?? 0;
                            $product->height = $productData['height'] ?? 0;
                            $product->width = $productData['width'] ?? 0;
                            $product->weight = $productData['weight'] ?? 0;
                            $product->save();

                            $this->info("Обновление размеров и весов продукта {$productData['name']} заврешено!");
                        } catch (\Exception $exception) {
                            report($exception);
                            $this->error($exception->getMessage());
                        }

                    }
                }
            });
        }
        return 0;
    }
}
