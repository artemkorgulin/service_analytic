<?php

namespace App\Console\Commands;

use App\Models\WbBarcode;
use App\Models\WbBarcodeStock;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Services\InnerService;
use App\Services\Wildberries\Client;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Swoole\Coroutine\run;

class UpdateWildberriesStocks extends Command
{
    /** @var string  wb_product_barcodes */
    protected static $tableWbProductBarcodes = 'wb_product_barcodes';
    protected static $tableWbTemporaryProducts = 'wb_temporary_products';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:update-stocks';
    /** @var int get elements per page form request */
    protected static $perPage = 1000;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Wildberries stocks';

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
        $accountIds = self::getAllWilberriesAccounts();
        $today = date('Y-m-d', time());
        foreach ($accountIds as $accountId) {
            $account = (new InnerService())->getAccount($accountId);
            $page = 0;
            $extraCaseForUpload = false;

            try {
                $client = new Client($account['platform_client_id'], $account['platform_api_key']);
            } catch (\Exception $exception) {
                report($exception);
                $this->error($exception->getMessage());
                continue;
            }

            $this->info("Try to get info for stock rest for account {$account['title']}");
            do {
                $response = self::getRestStock($client, $page, self::$perPage);
                // If Wildberries send us null stock or null total
                if (!$response || $response['stocks'] === [] || ($response['total'] === 0 && $page <= 1)) {
                    break;
                }

                $page++;

                // Reset array
                $stockRecords = [];
                foreach ($response['stocks'] as $item) {
                    WbBarcode::updateOrCreate(
                        ['barcode' => $item['barcode']],
                        ['subject' => $item['subject'], 'brand' => $item['brand'], 'name' => $item['name'],
                            'size' => $item['size'], 'barcode' => $item['barcode'],
                            'barcodes' => $item['barcodes'], 'article' => $item['article'],
                            'used' => true, 'quantity' => $item['stock']]
                    );
                    $stockRecords[] = [
                        'barcode' => $item['barcode'],
                        'check_date' => $today,
                        'quantity' => $item['stock'],
                        'warehouse_name' => $item['warehouseName'],
                        'warehouse_id' => $item['warehouseId'],
                    ];
                }

                foreach ($stockRecords as $stockRecord) {
                    WbBarcodeStock::updateOrCreate(
                        ['barcode' => $stockRecord['barcode'], 'check_date' => $stockRecord['check_date']],
                        [
                            'barcode' => $stockRecord['barcode'],
                            'check_date' => $stockRecord['check_date'],
                            'quantity' => $stockRecord['quantity'],
                            'warehouse_name' => $stockRecord['warehouse_name'],
                            'warehouse_id' => $stockRecord['warehouse_id'],
                        ]);
                }

            } while (!empty($response['stocks']));

            $this->info("Update only products nomenclatures (present or not in warehouse)");
            self::getAndUpdateRestStockForNomenclatures($client);
        }

        self::updateWildberriesProductsQuantities();

        return 0;
    }


    /**
     * Get information about present products in stocks it only for extra key only
     * @param array $account
     * @param $type
     * look https://suppliers-api.wildberries.ru/swagger/index.html#/%D0%A6%D0%B5%D0%BD%D1%8B/get_public_api_v1_info
     * for quantity
     * 2 - товар с нулевым остатком, 1 - товар с ненулевым остатком, 0 - товар с любым остатком
     * @return void
     */
    private static function getRestStockWithAccident(Client $client, $quantity = 1)
    {
        return $client->getInfo($quantity);
    }

    /**
     * Get all Wildberries accounts from wb_temporary_products table
     * @return array
     */
    private static function getAllWilberriesAccounts()
    {
        return DB::table('wb_temporary_products')
            ->select([DB::raw('account_id')])
            ->groupBy('account_id')
            ->pluck('account_id')->all();
    }

    /**
     * Get all information about Wildberries merchant stock
     * @param Client $client
     * @param int $page
     * @param int $perPage
     */
    private static function getRestStock(Client $client, int $page = 0, int $perPage = 1000)
    {
        try {
            return $client->getStocks(['skip' => $page * $perPage, 'take' => $perPage]);
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
            return false;
        }
    }

    /**
     * Get all information about Wildberries merchant stock
     * @param Client $client
     */
    private static function getAndUpdateRestStockForNomenclatures(Client $client)
    {
        try {
            $nomenclaturesInStock = collect($client->getInfo(1));
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        run(function () use ($nomenclaturesInStock) {
            foreach ($nomenclaturesInStock->chunk(self::$perPage) as $items) {
                go(function () use ($items) {
                    WbNomenclature::whereIn('nm_id', $items->pluck('nmId')->toArray())->update(['quantity' => 1]);
                });
            }
        });
        $nomenclaturesOutOfStock = collect($client->getInfo(2));
        run(function () use ($nomenclaturesOutOfStock) {
            foreach ($nomenclaturesOutOfStock->chunk(self::$perPage) as $items) {
                go(function () use ($items) {
                    WbNomenclature::whereIn('nm_id', $items->pluck('nmId')->toArray())->update(['quantity' => 0]);
                });
            }
        });
    }

    /**
     * Update Wildberries products quantities
     */
    public static function updateWildberriesProductsQuantities()
    {
        foreach (WbProduct::all() as $product) {
            try {
                $product->quantity = $product->getQuantity($product->data);
                $product->save();
            } catch (\Exception $exception) {
                report($exception);
                ExceptionHandlerHelper::logFail($exception);
            }
        }
    }

}
