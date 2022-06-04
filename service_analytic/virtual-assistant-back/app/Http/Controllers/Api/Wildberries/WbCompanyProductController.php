<?php
namespace App\Http\Controllers\Api\Wildberries;

use App\Http\Controllers\Api\CompanyProductController;
use App\Http\Requests\Company\CreateWbProductsByImtIdsRequest;
use Illuminate\Http\Request;


class WbCompanyProductController extends CompanyProductController
{

    /**
     * Constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        self::$service =  'App\Services\Wildberries\WildberriesCompanyProductService';
        parent::__construct($request);
    }

    /**
     * Wildberries products create by imtIds (cart id for Wildberries)
     * @param CompanyModelRequest $request
     * @return mixed
     */
    public function createProductsByImtIds(CreateWbProductsByImtIdsRequest $request)
    {
        return (self::$service)::createProductsByImtIds((int)$request->get('userId'),
            (int)$request->get('accountId'), $request->get('imtIds'))
            ? response()->api_fail('Возникли ошибки при добавлении товаров', [], 422)
            : response()->api_success($request->get('imtIds'), 200);
    }

}


