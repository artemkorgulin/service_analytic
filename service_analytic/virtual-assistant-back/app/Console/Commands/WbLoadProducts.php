<?php

namespace App\Console\Commands;

use App\Classes\Helper;
use App\Jobs\DashboardUpdateJob;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Services\Analytics\WbAnalyticsService;
use App\Models\WbTemporaryProduct;
use App\Services\InnerService;
use App\Services\Wildberries\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class WbLoadProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:load_products';

    /**
     * Database step to get information about products in Wildberries
     * @var int
     */
    protected $exportStep = 500;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка (перезагрузка/обновление) продуктов Wildberries для всех аккаунтов';

    /**
     * Create a new command instance.
     *
     * @param WbAnalyticsService $wbAnalyticsService
     * @return void
     */
    public function __construct(private WbAnalyticsService $wbAnalyticsService)
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
        // Get all accounts
        foreach (WbProduct::select(['account_id'])
            ->where('account_id', '<>', '')->whereNotNull('account_id')->groupBy('account_id')->get() as $a) {
            $innerService = new InnerService();
            $account = $innerService->getAccount($a->account_id);
            if (is_bool($account)) {
                continue;
            }
            $wbClient = new Client($account['platform_client_id'], $account['platform_api_key']);
            $this->info("Получаем информацию о номенклатурах Wildberries для аккаунта {$account['platform_client_id']}");
            try {
                $nomenclatures = collect($wbClient->getInfo());
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                continue;
            }
            $bar = $this->output->createProgressBar(count($nomenclatures));
            $bar->start();
            foreach ($nomenclatures->chunk($this->exportStep) as $nomenclaturesPart) {
                $records = [];
                foreach ($nomenclaturesPart as $nomenclature) {
                    if (!in_array($nomenclature->nmId, array_column($records, 'nm_id'))) {
                        $records[] = [
                            'user_id' => $account['user_id'] ?? null,
                            'account_id' => $account['id'],
                            'nm_id' => $nomenclature->nmId,
                            'price' => $nomenclature->price,
                            'discount' => $nomenclature->discount,
                            'promocode' => $nomenclature->promoCode,
                        ];
                    }
                    if ($nomenclature->nmId) {
                        $quantity = WbNomenclature::where([
                            ['nm_id', $nomenclature->nmId],
                            ['account_id', $account['id']]
                        ])->sum('quantity');
                        WbTemporaryProduct::query()->where('nmid', $nomenclature->nmId)->update([
                            'quantity' => $quantity,
                        ]);
                    }
                    $bar->advance();
                }

                WbNomenclature::upsert($records, ['account_id', 'nm_id'], ['user_id', 'price', 'discount', 'promocode']);
            }
            $bar->finish();
            $this->info("\n\n");

            $this->info("Получаю информацию о продуктах (карточках товаров) Wildberries");
            $counted = WbProduct::where('account_id', $account)->count();
            $bar = $this->output->createProgressBar($counted);
            $bar->start();

            // Update product for Wildberries
            WbProduct::where('account_id', $a->account_id)->chunk($this->exportStep, function ($products) use ($account, $bar) {
                $productAnalytics = $this->getProductRating($products);
                foreach ($products as $product) {
                    $wbClient = new Client($account['platform_client_id'],
                        $account['platform_api_key']);
                    try {
                        $response = $wbClient->getCardByImtID($product->imt_id);
                    } catch (\Exception $exception) {
                        Log::error($exception->getMessage(), [$account['id']]);
                        break;
                    }
                    $card = $response['result']['card'] ?? null;

                    if ($card === null) {
                        Log::channel('console')->error('Error in Wildberries card');
                        Log::channel('console')->info(print_r($response, true));
                        $bar->advance();
                        continue;
                    }

                    if ($product !== null) {
                        $updateData = [
                            'card_id' => $card['id'],
                            'imt_id' => $card['imtId'],
                            'card_user_id' => $card['userId'],
                            'supplier_id' => $card['supplierId'],
                            'imt_supplier_id' => $card['imtSupplierId'],
                            'title' => Helper::wbCardGetTitle($card),
                            'object' => $card['object'],
                            'parent' => $card['parent'],
                            'country_production' => $card['countryProduction'],
                            'supplier_vendor_code' => $card['supplierVendorCode'],
                            'data' => json_encode($card)
                        ];

                        if (array_key_exists($product['sku'], $productAnalytics)) {
                            $updateData['rating'] = $productAnalytics[$product['sku']];
                        }
                        $product->update($updateData);
                        $product->save();

                        // Обновляю номенклатуры
                        $data = Helper::getUsableData($product);
                        $nomenclatureNmIds = Helper::wbCardGetNmIds($data);
                        $nomenclatureIds = WbNomenclature::where('account_id', $account['id'])
                            ->whereIn('nm_id', $nomenclatureNmIds)
                            ->pluck('id')->toArray();
                        $product->nomenclatures()->sync($nomenclatureIds);
                    }
                    $bar->advance();
                }
            });
            $bar->finish();
            $this->info("\n\n");
        }

        DashboardUpdateJob::dispatch(3)->delay(now()->addMinutes(3));

        return 0;
    }

    /**
     * @param Collection $products
     * @return array [sku] => rating
     */
    private function getProductRating(Collection $products): array
    {
        $productAnalytics = $this->wbAnalyticsService
            ->getProductsRating($products->pluck('sku')->toArray());

        return $productAnalytics['data'][0] ?? [];
    }
}
