<?php

namespace App\Services\Ozon;

use App\DTO\Ozon\Contracts\DTOInterface;
use App\DTO\Ozon\ProductChangeHistoryDTO;
use App\Models\ProductChangeHistory;

class OzonProductChangeHistoryService
{
    /**
     * @param  ProductChangeHistory  $changeHistory
     */
    public function __construct(private ProductChangeHistory $changeHistory)
    {
        //
    }

    /**
     * @param  array  $productArray
     * @param  bool  $isSend
     * @param  int  $statusId
     * @return ProductChangeHistory
     */
    public function createHistoryProductFromDTO(ProductChangeHistoryDTO $createHistoryDTO): ProductChangeHistory
    {
        if (($createHistoryDTO instanceof DTOInterface) === false) {
            throw new \Exception('Use DTO object.');
        }

        $productHistoryArray = $createHistoryDTO->toArray();

        return $this->changeHistory::create([
            'product_id' => $productHistoryArray['product_id'],
            'name' => $productHistoryArray['name'],
            'status_id' => $productHistoryArray['status_id'],
            'task_id' => $productHistoryArray['task_id'],
            'is_send' => $productHistoryArray['is_send'],
            'request_data' => $productHistoryArray['request_data'],
            'response_data' => $productHistoryArray['response_data'],
        ]);
    }

}
