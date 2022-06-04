<?php

namespace App\Services\Wildberries;

use App\Jobs\Wildberries\WildberriesClearPriceJsonFilesJob;
use App\Services\InnerService;
use App\Services\Json\JsonService;
use Illuminate\Support\Facades\Cache;

class WildberriesAccountPricePlatformService
{
    const PRICE_CACHE_TTL = 600;
    const PRICE_CACHE_KEY = 'wb_price_account_';
    const PRICE_ITEMS_FILE_NAME = 'nomenclatures_in_stock_';

    private int $accountId;
    private array $account;
    private JsonService $jsonService;

    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
        $this->account = (new InnerService())->getAccount($accountId);
        $this->jsonService = new JsonService();
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function updateDataInFile()
    {
        $wbApi = new Client($this->account['platform_client_id'], $this->account['platform_api_key']);

        $url = $wbApi->apiMethodUrls['getInfo'];
        $nomenclaturesData = $wbApi->getJsonStream($url . '?quantity=0');

        $this->jsonService->saveJsonToFile($this->getPriceFileName(), $nomenclaturesData);

        WildberriesClearPriceJsonFilesJob::dispatch()->delay(now()->addSeconds(self::PRICE_CACHE_TTL));
    }

    /**
     * @param array $nmIds
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPriceFromFile(array $nmIds)
    {
        if (!$this->checkUpdateFilePriceCache()) {
            $this->updateDataInFile();
        }

        $priceArray = [];

        foreach ($this->jsonService->getJsonItems($this->getPriceFileName()) as $item) {
            if (in_array($item->nmId, $nmIds)) {
                $priceArray[] = [
                    'nmId' => $item->nmId,
                    'price' => $item->price,
                    'discount' => $item->discount,
                ];
            }
        }

        return $priceArray;
    }

    /**
     * @return bool
     */
    protected function checkUpdateFilePriceCache()
    {
        if (!Cache::get($this->getCacheKey())) {
            Cache::put(self::PRICE_CACHE_KEY . $this->accountId, 1, self::PRICE_CACHE_TTL);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return self::PRICE_CACHE_KEY . $this->accountId;
    }

    /**
     * @return string
     */
    public function getPriceFileName()
    {
        return self::PRICE_ITEMS_FILE_NAME . $this->accountId;
    }

}
