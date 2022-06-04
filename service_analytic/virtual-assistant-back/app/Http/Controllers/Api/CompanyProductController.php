<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CreateByIdsProductsRequest;
use App\Http\Requests\Company\DeleteProductsForAllAccountUsersRequest;
use App\Http\Requests\Company\DeleteProductsRequest;
use App\Http\Requests\Company\MoveProductsRequest;

class CompanyProductController extends Controller
{

    public static ?string $service;



    /**
     * Copy one product from one account to another for product
     * @param MoveProductsRequest $request
     * @return mixed
     */
    public function moveProducts(MoveProductsRequest $request): mixed
    {
        return (self::$service)::moveProducts((int)$request->get('sourceUserId'),
            (int)$request->get('recipientUserId'), (int)$request->get('accountId'))
            ? response()->api_success('Товары перемещены между пользователями', 200)
            : response()->api_fail('Ошибка при копировании товара аккаунта', [], 422);

    }

    /**
     * Delete all products for user in account
     * @param DeleteProductsRequest $request
     * @return mixed
     */
    public function deleteProducts(DeleteProductsRequest $request): mixed
    {
        return (self::$service)::deleteProducts((int)$request->get('userId'), (int)$request->get('accountId'))
            ? response()->api_success('Товары удалены', 200)
            : response()->api_fail('Ошибка при удалении товаров', [], 422);
    }

    /**
     * Delete all products for user in account
     * @param DeleteProductsForAllAccountUsersRequest $request
     * @return mixed
     */
    public function deleteProductsForAllAccountUsers(DeleteProductsForAllAccountUsersRequest $request): mixed
    {
        return (self::$service)::deleteProducts(0, (int)$request->get('accountId'))
            ? response()->api_success('Товары удалены', 200)
            : response()->api_fail('Ошибка при удалении товаров', [], 422);
    }

    /**
     * Ozon products create by temporary products is
     * @param CreateByIdsProductsRequest $request
     * @return mixed
     */
    public function createProductsByIds(CreateByIdsProductsRequest $request): mixed
    {
        return (self::$service)::createProductsByIds((int)$request->get('userId'),
            (int)$request->get('accountId'), $request->get('ids'))
            ? response()->api_fail('Возникли ошибки при добавлении товаров', [], 422)
            : response()->api_success($request->get('ids'), 200);
    }

}


