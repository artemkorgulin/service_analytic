<?php

namespace App\Services\V2;

use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;

class OzonStocksService
{
    /**
     * Update stocks for Ozon account
     *
     * @param $accountId
     * @param $platformClientId
     * @param $platformApiKey
     * @return void
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    public function updateStocks($accountId, $platformClientId, $platformApiKey): void
    {
        $ozTemporaryProducts = $this->getQuantitiesByExternalIdForAccount(OzTemporaryProduct::class, $accountId);
        $ozProducts = $this->getQuantitiesByExternalIdForAccount(OzProduct::class, $accountId);
        $pageSize = 1000;
        $page = 1;
        do {
            $stocksFromOzon = $this->getStocksFromOzon($platformClientId, $platformApiKey, $page, $pageSize);
            if (!empty($stocksFromOzon)) {
                foreach ($stocksFromOzon as $externalId => $stocks) {
                    if (isset($ozTemporaryProducts[$externalId])) {
                        $this->updateProductQuantities(OzTemporaryProduct::class, $externalId, $accountId, $stocks);
                    }
                    if (isset($ozProducts[$externalId])) {
                        $this->updateProductQuantities(OzProduct::class, $externalId, $accountId, $stocks);
                    }
                }
            }
            $page++;
        } while ($stocksFromOzon);
    }

    /**
     * Get stocks from Ozon by API
     *
     * @param $platformClientId
     * @param $platformApiKey
     * @param $page
     * @param $pageSize
     * @return array
     * @throws \App\Exceptions\Ozon\OzonServerException
     */
    public function getStocksFromOzon($platformClientId, $platformApiKey, $page, $pageSize): array
    {
        $result = [];
        $ozonApiClient = new OzonApi($platformClientId, $platformApiKey);
        $stocks = $ozonApiClient->ozonRepeat('getStocks', $page, $pageSize)['data']['result'];
        $this->handleStocks($stocks['items'], $result);
        return $result;
    }

    /**
     * @param array $stocks
     * @param array $result
     * @return void
     */
    private function handleStocks(array $stocks, array &$result): void
    {
        foreach ($stocks as $stock) {
            $productId = $stock['product_id'];
            $productStocks = $stock['stocks'];
            if (!empty($productStocks)) {
                foreach ($productStocks as $productStock) {
                    $result[$productId][$productStock['type']] = (int)$productStock['present'];
                }
            }
        }
    }

    /**
     * Get quantities by externalId for account
     *
     * @param OzProduct|OzTemporaryProduct $model
     * @param int $accountId
     * @return array
     */
    private function getQuantitiesByExternalIdForAccount($model, int $accountId): array
    {
        $quantities = [];
        $ozProducts = $model::select([
            'external_id',
            'quantity_fbo',
            'quantity_fbs'
        ])->where('account_id', $accountId)->get();
        if ($ozProducts->isNotEmpty()) {
            foreach ($ozProducts as $product) {
                $quantities[$product->external_id] = [
                    'fbo' => $product->quantity_fbo,
                    'fbs' => $product->quantity_fbs,
                ];
            }
        }
        return $quantities;
    }

    /**
     * Update product quantities in DB
     *
     * @param OzProduct|OzTemporaryProduct $model
     * @param int $externalId
     * @param int $accountId
     * @param array $stocks
     */
    private function updateProductQuantities($model, int $externalId, int $accountId, array $stocks): void
    {
        $model::where('external_id', $externalId)
            ->where('account_id', $accountId)
            ->update([
                'quantity_fbo' => $stocks['fbo'] ?? 0,
                'quantity_fbs' => $stocks['fbs'] ?? 0
            ]);
    }
}
