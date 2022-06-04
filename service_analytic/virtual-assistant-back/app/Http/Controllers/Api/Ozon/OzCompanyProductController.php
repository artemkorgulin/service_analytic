<?php

namespace App\Http\Controllers\Api\Ozon;

use App\Http\Requests\Company\CreateOzProductsByExternalIdsRequest;
use App\Http\Controllers\Api\CompanyProductController;
use Illuminate\Http\Request;

class OzCompanyProductController extends CompanyProductController
{

    /**
     * Constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        self::$service = 'App\Services\Ozon\OzonCompanyProductService';
        parent::__construct($request);
    }

    /**
     * Ozon products create by products externalId
     * @param CreateOzProductsByExternalIdsRequest $request
     * @return mixed
     */
    public function createProductsByExternalIds(CreateOzProductsByExternalIdsRequest $request)
    {
        return (self::$service)::createProductsByExternalIds((int)$request->get('userId'),
            (int)$request->get('accountId'), $request->get('externalIds'))
            ? response()->api_fail('Возникли ошибки при добавлении товаров', [], 422)
            : response()->api_success($request->get('externalIds'), 200);
    }

}


