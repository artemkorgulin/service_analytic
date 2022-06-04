<?php

namespace App\Services\Ozon;

use App\Models\OzProduct;

class OzonApiStatusService
{
    public function __construct()
    {
        //
    }

    /**
     * @param  OzProduct  $ozProduct
     * @param $platformId
     * @param $apiKey
     * @return false|string
     */
    public function checkProductUploadStatus($productInfoResponse)
    {
        if (!isset($productInfoResponse['statusCode']) || $productInfoResponse['statusCode'] !== 200) {
            return false;
        }

        if (!isset($productInfoResponse['data']['result']['status'])) {
            return false;
        }

        return $this->arrayToStatusMapper($productInfoResponse['data']['result']['status']);
    }

    /**
     * @TODO разобраться с логикой статусов, данное решение не до конца охватывает ее.
     *
     * @param $productInfo
     * @return string
     */
    public function arrayToStatusMapper($productInfo)
    {
        $productStatus = 'moderating';
        if ($productInfo['is_failed']) {
            $productStatus = 'error';
        } elseif (empty($productInfo['moderate_status'])) {
            $productStatus = 'moderating';
        } elseif ($productInfo['moderate_status'] === 'approved') {
            $productStatus = 'processed';
        } elseif ($productInfo['validation_state'] !== 'success') {
            $productStatus = 'failed_validation';
        }

        return $productStatus;
    }
}
