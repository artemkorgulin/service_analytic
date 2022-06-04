<?php

namespace App\Jobs;

use App\Classes\Helper;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use App\Services\InnerService;
use App\Services\Wildberries\Client;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Exception;
use JsonMachine\Items;
use App\Services\Json\JsonService;

class CreateProductsForWildberriesAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $accountId;
    protected $accountClientId;
    protected $accountApiKey;
    protected $user;
    protected $wbApiClient;
    protected $itemsPerPage = 2000;
    protected $chunkSize = 500;
    public $tries = 1;
    protected $totalAddedProducts = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, array $accountParams)
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
     * @param JsonService $jsonService
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(JsonService $jsonService)
    {
        DB::connection()->unsetEventDispatcher();

        Log::channel('queues')->info('Начало загрузки товаров с WB. Аккаунт ' . $this->accountClientId);
        $this->wbApiClient = new Client($this->accountClientId, $this->accountApiKey);

        // initially upload wb nomenclatures
        $this->setUserNotification('marketplace.account_product_upload_start');

        $url = $this->wbApiClient->apiMethodUrls['getInfo'];
        $nomenclaturesInStockStream = $this->wbApiClient->getJsonStream($url . '?quantity=1');
        $filenameInStock = "nomenclatures_in_stock_$this->accountId";
        if (!$jsonService->saveJsonToFile($filenameInStock, $nomenclaturesInStockStream)) {
            throw new Exception('Не удалось сохранить json файл номенклатур наличия остатков #1');
        }

        $nomenclaturesOutStockStream = $this->wbApiClient->getJsonStream($url . '?quantity=2');
        $filenameOutStock = "nomenclatures_out_stock_$this->accountId";
        if (!$jsonService->saveJsonToFile($filenameOutStock, $nomenclaturesOutStockStream)) {
            throw new Exception('Не удалось сохранить json файл номенклатур наличия остатков #2');
        }


        $inStockItems = $jsonService->getJsonItems($filenameInStock);
        $this->saveNomenclatures($inStockItems, 1);
        $jsonService::removeJson($filenameInStock);

        $outStockItems = $jsonService->getJsonItems($filenameOutStock);
        $this->saveNomenclatures($outStockItems, 0);
        $jsonService::removeJson($filenameOutStock);


        Log::channel('queues')->info('Номенклатуры WB сохранена в базу данных. Аккаунт ' . $this->accountClientId);
        // work with temporary Wildberries products

        $itemsPerPage = 1000;

        $totalPages = $this->getTotalPages($itemsPerPage) + 10;

        for ($i = 0; $i < $totalPages; $i++) {

            $filename = sprintf("wb_products_%d_%d", $this->accountId, $i);
            $this->downloadJson($itemsPerPage, $itemsPerPage * $i, $jsonService, $filename);
        }

        $errors = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $filenameProducts = "wb_products_$this->accountId" . '_' . $i;
            $productItems = $jsonService->getJsonItems($filenameProducts, '/result/cards');
            try {
                foreach ($productItems as $item) {
                    break;
                }
                $this->saveCards($productItems);
            } catch (\Exception $exception) {
                $errors[] = $itemsPerPage * $i;
            }
            $jsonService::removeJson($filenameProducts);
        }

        $this->binaryFindErrorProduct($errors, $jsonService, $itemsPerPage);

        $this->totalAddedProducts = $this->countProducts();
        if (!empty($this->totalAddedProducts)) {
            $this->setUserNotification('marketplace.account_product_upload_success', ['counted' => $this->totalAddedProducts]);
        } else {
            $this->setUserNotification('marketplace.account_product_upload_fail');
        }

        Log::channel('queues')->info('Окончание загрузки товаров с WB. Аккаунт ' . $this->accountClientId);
    }

    private function binaryFindErrorProduct(array $errorPages, JsonService $jsonService, $perPage = 1000)
    {
        if ($perPage < 2) {
            return;
        }
        $filenames = [];
        $errors = [];
        $newPerPage = (int)$perPage / 2;
        foreach ($errorPages as $offset) {
            $filenames[$offset] = $filename = sprintf('wb_products_%d_offset_%d', $this->accountId, $offset);
            $this->downloadJson($newPerPage, $offset + $newPerPage, $jsonService, $filename);
            $filenames[$offset + $newPerPage] = $filename = sprintf('wb_products_%d_offset_%d', $this->accountId, $offset + $newPerPage);
            $this->downloadJson($newPerPage, $offset + $newPerPage, $jsonService, $filename);
        }

        foreach ($filenames as $offset => $filename) {
            $productItems = $jsonService->getJsonItems($filename, '/result/cards');
            try {
                // Empty foreach for check items errors and catch (because we're using external package)
                foreach ($productItems as $item) {
                    break;
                }
                $this->saveCards($productItems);
            } catch (\Exception $exception) {
                $errors[] = $offset;
            }
            $jsonService::removeJson($filename);
        }
        if ($errors) {
            $this->binaryFindErrorProduct($errors, $jsonService, $newPerPage);
        }
    }

    private function downloadJson($itemsPerPage, $offset, $jsonService, $filenameProducts)
    {
        $json = [
            "id" => time(),
            "jsonrpc" => "2.0",
            "params" => [
                "isArchive" => true,
                "query" => [
                    "limit" => $itemsPerPage,
                    "offset" => $offset,
                    "total" => 0
                ],
                "supplierID" => $this->accountClientId,
                "withError" => false
            ]
        ];
        $url = $this->wbApiClient->apiMethodUrls['getCardList'];
        $productStream = $this->wbApiClient->getJsonStream($url, 'POST', $json);
        if (!$jsonService->saveJsonToFile($filenameProducts, $productStream)) {
            throw new Exception('Не удалось сохранить json файл продуктов для пользователя');
        }
    }

    /**
     * Return total pages from WB api
     * @param int $itemsPerPage
     * @return int
     */
    private function getTotalPages(int $itemsPerPage): int
    {
        $pages = 1;
        $totalProducts = $this->wbApiClient->getCardList(1, 0, true);
        if ($totalProducts && $itemsPerPage) {
            $pages = ceil($totalProducts / $itemsPerPage);
        }
        return (int)$pages;
    }

    /**
     * Get WB cards
     *
     * @param int $page
     * @return false|Collection
     */
    private function getNomenclatureCards(int $items, int $offset)
    {
        $retryCount = 0;

        do {
            try {
                $cards = collect($this->wbApiClient->getCardList($items, $offset));
            } catch (\GuzzleHttp\Exception\ConnectException $exception) {
                // log the error here
                report($exception);
                sleep(10);
                Log::channel('queues')
                    ->warning('guzzle_connect_exception', ['message' => $exception->getMessage(), ' Аккаунт ' . $this->accountClientId]);
            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                report($exception);
                sleep(10);
                Log::channel('queues')
                    ->warning('guzzle_connection_timeout', ['message' => $exception->getMessage(), ' Аккаунт ' . $this->accountClientId]);
            } catch (\Exception $exception) {
                report($exception);
                sleep(10);
                Log::channel('queues')
                    ->warning('common_exception', ['message' => $exception->getMessage(), ' Аккаунт ' . $this->accountClientId]);
            }

            // Do max 5 attempts
            if (++$retryCount === 5) {
                print("Ошибка получения товаров со страницы");
                return false;
            }
        } while ($cards->isEmpty());

        return $cards;
    }

    /**
     * Save products cards in DB
     *
     * @param Items $cards
     * @return int
     */
    private function saveCards(Items $cards)
    {
        $counted = 0;
        $removeOldProducts = [];
        $insertData = [];

        $account = (new InnerService())->getAccount($this->accountId);

        if (!isset($account['platform_client_id']) || !$account['platform_client_id']) {
            Log::channel('queues')->error('Not set platform_client_id for account id: ' . $this->accountId);
            return 0;
        }

        try {
            foreach ($cards as $card) {
                $card = json_decode(json_encode($card), true);

                if (!isset($card['id'])) {
                    continue;
                }

                $nomenclatures = Helper::wbCardGetNmIds($card) ?? [];
                $currentDateTime = Carbon::now()->toDateTimeString();

                $removeOldProducts[] = $card['imtId'];

                try {
                    $insertData[] = [
                        'account_id' => $this->accountId,
                        'account_client_id' => $this->accountClientId,
                        'card_id' => $card['id'],
                        'imt_id' => $card['imtId'],
                        'user_id' => $this->user['id'] ?? null,
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
                        'quantity' => WbNomenclature::whereIn('nm_id', Helper::wbCardGetNmIds($card) ?? [])->sum('quantity') ?? 0,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime
                    ];
                } catch (\Exception $exception) {
                    report($exception);
                    Log::channel('queues')->error($exception->getMessage(), [' Аккаунт ' . $this->accountClientId]);
                }
            }
            WbProduct::whereIn('imt_id', $removeOldProducts)->forceDelete();
            DB::table('wb_temporary_products')->upsert($insertData, ['imt_id'], ['account_id']);
        } catch (\Exception $exception) {

        }
        return $counted;
    }

    /**
     * Save WB nomenclatures
     * @param $nomenclatures
     */
    private function saveNomenclatures($nomenclatures, $inStockFeature = 0)
    {
        $records = [];

        if (empty($nomenclatures)) {
            return;
        }

        foreach ($nomenclatures as $nomenclature) {
            if (!in_array($nomenclature->nmId, array_column($records, 'nm_id'))) {
                $records[] = [
                    'user_id' => $this->user['id'] ?? null,
                    'account_id' => $this->accountId,
                    'nm_id' => $nomenclature->nmId,
                    'price' => $nomenclature->price,
                    'discount' => $nomenclature->discount,
                    'promocode' => $nomenclature->promoCode,
                    'quantity' => $inStockFeature,
                ];
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
     * Create notification from user
     *
     * @param string $notificationName
     * @param array $extParams
     */
    private function setUserNotification(string $notificationName, array $extParams = [])
    {
        $user = array_merge($this->user, ['lang' => 'ru']);
        $params = array_merge($extParams, ['marketplace' => 'Wildberries']);
        UsersNotification::dispatch($notificationName, [0 => $user], $params);
    }

    /**
     * Count account products
     *
     * @return int
     */
    private function countProducts(): int
    {
        $query = WbTemporaryProduct::select('sku')
            ->distinct()
            ->where('account_id', '=', $this->accountId);
        $getUsedSku = WbProduct::select('sku')
            ->distinct()
            ->where('user_id', '=', $this->user['id'])
            ->where('account_id', '=', $this->accountId)
            ->get();
        if (!$getUsedSku->isEmpty()) {
            $query = $query->whereNotIn(
                'sku',
                $getUsedSku->pluck('sku')->all()
            );
        }
        return $query->count();
    }
}
