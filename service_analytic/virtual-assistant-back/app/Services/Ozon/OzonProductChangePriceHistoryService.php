<?php

namespace App\Services\Ozon;

use App\Models\ProductPriceChangeHistory;

class OzonProductChangePriceHistoryService
{
    /**
     * @param  ProductPriceChangeHistory  $priceChangeHistory
     */
    public function __construct(private ProductPriceChangeHistory $priceChangeHistory)
    {
        //
    }

    /**
     * @param  float  $price
     * @param  int  $productId
     * @param  int  $productHistoryId
     * @param  float  $oldPrice
     * @param  bool  $sentFromVa
     * @param  bool  $isApplied
     * @param  string  $errors
     * @return ProductPriceChangeHistory
     */
    public function createPriceHistoryChanges(
        int $productId,
        int $productHistoryId,
        float $price,
        float $oldPrice = 0,
        string $errors = '',
        bool $sentFromVa = true,
        bool $isApplied = false,

    ): ProductPriceChangeHistory {
        $productChangePriceHistory = $this->priceChangeHistory::create([
            'price' => $price,
            'sent_from_va' => $sentFromVa,
            'is_applied' => $isApplied,
            'errors' => $errors,
            'product_id' => $productId,
            'product_history_id' => $productHistoryId,
            'old_price' => $oldPrice,
        ]);

        return $productChangePriceHistory;
    }


}
