<?php

namespace App\Services\Wildberries;


use App\Classes\Helper;
use App\Models\WbBarcodeStock;
use App\Models\WbProduct;
use App\Services\InnerService;
use App\Services\Wildberries\Client;
use Illuminate\Support\Facades\Log;

class WildberriesStocksService
{
    public string $today;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->today = date('Y-m-d', time());
    }

    /**
     * @param $accauntId
     * @param $platformClientId
     * @param $platformApiKey
     * @return void
     */
    public function updateProductStocks(WbProduct $product)
    {
        try {
            $account = (new InnerService())->getAccount($product->account_id);
            if (is_bool($account)) {
                return false;
            }
            $wbClient = new Client($account['platform_client_id'], $account['platform_api_key']);
            $barcodes = Helper::wbCardGetBarcodes($product->data);
            foreach ($barcodes as $barcode) {
                $stockByBarcode = $wbClient->getStocks(['search' => $barcode, 'skip' => 0, 'take' => 100]);
                if (!isset($stockByBarcode['stocks'][0]['barcode'])) {
                    continue;
                }
                foreach ($stockByBarcode['stocks'] as $stockRecord) {
                    WbBarcodeStock::updateOrCreate(
                        ['barcode' => $stockRecord['barcode'], 'check_date' => $this->today],
                        [
                            'barcode' => $stockRecord['barcode'],
                            'check_date' => $this->today,
                            'quantity' => $stockRecord['stock'],
                            'warehouse_name' => $stockRecord['warehouseName'],
                            'warehouse_id' => $stockRecord['warehouseId'],
                        ]);
                }
            }

            $product->quantity = $product->getQuantity($product->data);
            $product->save();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

    }


}
