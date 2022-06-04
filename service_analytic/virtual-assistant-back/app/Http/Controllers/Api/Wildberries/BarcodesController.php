<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Http\Requests\Wildberries\WildberriesBarcodeRequest;

class BarcodesController extends CommonController
{

    /**
     * Generation new barcodes
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function create(WildberriesBarcodeRequest $request)
    {
        $barcodeResponse = $this->client->cardGetBarcodes((int)$request->get('quantity'));
        if (isset ($barcodeResponse->result->barcodes)) {
            return response()->api_success(implode($barcodeResponse->result->barcodes, ','), 200);
        } else {
            return response()->api_fail(__('Error in Wildberries barcode generator'), [], 500);
        }
    }

}
