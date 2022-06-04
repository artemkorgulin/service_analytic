<?php

namespace App\Console\Commands;

use App\Classes\Helper;
use App\Models\WbNomenclature;
use App\Models\WbTemporaryProduct;
use App\Services\InnerService;
use App\Services\Json\JsonService;
use App\Services\Wildberries\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateWildberriesProductForAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:update-products-for-accounts {--account_id= : Номер аккаунта, товары для которого необходимо перегрузить }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление товаров для аккаунтов Wildberries';

    /** @var int platform id */
    protected $platformId = 3;

    /** @var int products in page for one request */
    protected $itemsPerPage = 1000;

    /** @var int chunk size for insert or create Ozon accounts */
    protected $chunkSize = 100;

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
     */
    private function updateTemporaryProductAccount($account)
    {
        $jsonService = (new JsonService());
        if (!isset($account['platform_client_id']) || !$account['platform_client_id']
            || !isset($account['platform_api_key']) || !$account['platform_api_key']) {
            return false;
        }

        $wbApi = new Client($account['platform_client_id'], $account['platform_api_key']);

        $url = $wbApi->apiMethodUrls['getInfo'];
        $nomenclaturesInStockStream = $wbApi->getJsonStream($url . '?quantity=1');
        $filenameInStock = sprintf("nomenclatures_in_stock_%s", $account['id']);
        if (!$jsonService->saveJsonToFile($filenameInStock, $nomenclaturesInStockStream)) {
            throw new \Exception('Не удалось сохранить json файл номенклатур наличия остатков #1');
        }

        $nomenclaturesOutStockStream = $wbApi->getJsonStream($url . '?quantity=2');
        $filenameOutStock = sprintf("nomenclatures_out_stock_%s", $account['id']);
        if (!$jsonService->saveJsonToFile($filenameOutStock, $nomenclaturesOutStockStream)) {
            throw new \Exception('Не удалось сохранить json файл номенклатур наличия остатков #2');
        }

        $inStockItems = $jsonService->getJsonItems($filenameInStock);
        $this->saveNomenclatures($inStockItems, $account, 1);
        $jsonService::removeJson($filenameInStock);

        $outStockItems = $jsonService->getJsonItems($filenameOutStock);
        $this->saveNomenclatures($outStockItems, $account, 0);
        $jsonService::removeJson($filenameOutStock);

        unset($nomenclaturesToLoad, $nomenclaturesExist, $diff1, $diff2, $nomenclaturesPart);
        $this->info(__('Wildberries nomeclatures save to database for :account.', ['account' => $account['platform_client_id']]));
        Log::channel('console')->info(__('Wildberries nomeclatures save to database for :account.', ['account' => $account['platform_client_id']]));

        $page = 0;

        do {

            $this->info(__('Start to work with page # :page and account :account ',
                [
                    'page' => $page + 1,
                    'account' => $account['platform_client_id']
                ]));

            $cards = $this->getCards($wbApi, $page);

            $page++;

            if (!$cards) {
                break;
            }

            $countProducts = $this->saveCards($cards, $account);

            unset($cards);
        } while ($countProducts > 0);
        unset($cards);
        $this->info(__('Temporary product was loaded for account :account.', ['account' => $account['platform_client_id']]));
        Log::channel('console')->info(__('Temporary product was loaded for account :account.', ['account' => $account['platform_client_id']]));
    }

    /**
     * Save WB nomenclatures
     * @param $nomenclatures
     */
    private function saveNomenclatures($nomenclatures, $account, $inStockFeature = 0)
    {
        $records = [];

        if (empty($nomenclatures)) {
            return;
        }

        foreach ($nomenclatures as $nomenclature) {
            if (!in_array($nomenclature->nmId, array_column($records, 'nm_id'))) {
                $records[] = [
                    'user_id' => $account['user_id'] ?? null,
                    'account_id' => $account['id'] ?? null,
                    'nm_id' => $nomenclature->nmId,
                    'price' => $nomenclature->price,
                    'discount' => $nomenclature->discount,
                    'promocode' => $nomenclature->promoCode,
                    'quantity' => $inStockFeature,
                ];
                WbTemporaryProduct::query()->where('nmid', $nomenclature->nmId)->update([
                    'quantity' => $inStockFeature,
                ]);
            }

            if (count($records) >= 500) {
                WbNomenclature::upsert($records, ['account_id', 'nm_id'], ['user_id', 'price', 'discount', 'promocode', 'quantity']);
                $records = [];
            }
        }

        if (!empty($records)) {
            WbNomenclature::upsert($records, ['account_id', 'nm_id'], ['user_id', 'price', 'discount', 'promocode', 'quantity']);
        }
    }

    /**
     * Get Wildberries cards
     * @param int $page
     * @return false|Collection
     */
    private function getCards($wbApi, int $page)
    {
        $retryCount = 0;

        do {

            try {
                $cards = collect($wbApi->getCardList($this->itemsPerPage, $page * $this->itemsPerPage));
            } catch (\GuzzleHttp\Exception\ConnectException $exception) {
                // log the error here
                report($exception);
                sleep(10);
                Log::channel('console')
                    ->warning('guzzle_connect_exception', ['message' => $exception->getMessage()]);
            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                report($exception);
                sleep(10);
                Log::channel('console')
                    ->warning('guzzle_connection_timeout', ['message' => $exception->getMessage()]);
            } catch (\Exception $exception) {
                report($exception);
                sleep(10);
                Log::channel('console')
                    ->warning('common_exception', ['message' => $exception->getMessage()]);
            }

            // Do max 5 attempts
            if (++$retryCount === 5) {
                return false;
            }
        } while (!isset($cards) || $cards->isEmpty());

        return $cards;
    }

    /**
     * Save products cards in database
     *
     * @param Collection $cards
     * @return int
     */
    private function saveCards(Collection $cards, $account)
    {
        $counted = 0;
        if ($cards->isEmpty()) {
            return $counted;
        }
        foreach ($cards->chunk($this->chunkSize) as $chunk) {
            $counted = $this->saveCardsChunk($chunk, $account);
        }
        return $counted;
    }

    /**
     * Save cards chunk
     * @param $chunk
     * @param $account
     */
    private function saveCardsChunk($chunk, $account)
    {
        foreach ($chunk as $count => $card) {
            $card = json_decode(json_encode($card), true);
            if (!isset($card['imtId'])) {
                return 0;
            }
            $nomenclatures = Helper::wbCardGetNmIds($card) ?? [];
            $currentDateTime = Carbon::now()->toDateTimeString();
            $record = WbTemporaryProduct::where(['account_id' => $account['id'], 'imt_id' => $card['imtId']])
                ->withTrashed()->first();
            $wbNmIds = Helper::wbCardGetNmIds($card);
            $quantity = WbNomenclature::whereIn('nm_id', $wbNmIds)->sum('quantity') ?? 0;

            $insertData = [
                'card_id' => $card['id'],
                'account_client_id' => $account['platform_client_id'],
                'user_id' => $account['user_id'] ?? 0,
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
                'quantity' => $quantity,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ];

            if ($record && isset($nomenclatures[0])) {
                // updated only if data in record changed
                if (
                    // Вот это фигнёй как "!==", лучше не заниматься потому что у нас цены и ID хранятся как числа у
                    // маркетплейсов все возвращается как "2313.00" или "34342423434" в общем как строки
                    // Особенно касаемо Wildberries приводить одно в другое, как-то неразумно лучше сравнивать != или ==
                    $record->card_id != $card['id'] ||
                    $record->card_user_id != $card['userId'] ||
                    $record->supplier_id != $card['supplierId'] ||
                    $record->supplier_user_id != $card['imtSupplierId'] ||
                    $record->title != Helper::wbCardGetTitle($card) ||
                    $record->brand != Helper::wbCardGetBrand($card) ||
                    $record->nmid != $nomenclatures[0] ||
                    $record->sku != $nomenclatures[0] ||
                    $record->image != Helper::wbCardGetPhoto($card) ||
                    $record->price != Helper::wbCardGetPrice($card) ||
                    $record->object != $card['object'] ||
                    $record->parent != $card['parent'] ||
                    $record->country_production != $card['countryProduction'] ||
                    $record->supplier_vendor_code != $card['supplierVendorCode'] ||
                    $record->url != Helper::wbCardGetUrl($card) &&
                    Helper::wbCardGetTitle($card)
                ) {
                    unset($insertData['created_at']);
                    $record->update($insertData);
                    $this->info(__('Update Wildberries card :imt_id - product ":name".',
                        ['imt_id' => $card['imtId'], 'name' => $insertData['title']]));
                    Log::channel('console')->info(__('Update Wildberries card :imt_id - product ":name".',
                        ['imt_id' => $card['imtId'], 'name' => $insertData['title']]));
                }
            } else {
                $insertData['imt_id'] = $card['imtId'];
                $insertData['account_id'] = $account['id'];
                DB::table('wb_temporary_products')->insert($insertData);
                $this->info(__('Create Wildberries card :imt_id - product ":name".',
                    ['imt_id' => $card['imtId'], 'name' => $insertData['title']]));
                Log::channel('console')->info(__('Create Wildberries card :imt_id - product ":name".',
                    ['imt_id' => $card['imtId'], 'name' => $insertData['title']]));
            }
        }
        unset($insertData);

        return $count ?? 0;
    }
}
