<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Ozon\OzonServerException;
use App\Exceptions\Product\ProductException;
use Exception;
use Illuminate\Http\Request;

interface ProductsInterface
{

    public const productOnPage = 20;

    /**
     * Создание продукта
     * @param Request $request
     * @return mixed
     */
    public function createProduct(Request $request);

    /**
     * Массовая загрузка товара
     *
     * @param Request $request
     * @return mixed
     */
    public function massAddProducts(Request $request);

    /**
     * Добавление товара
     *
     * @param Request $request
     * @return mixed
     */
    public function addProduct(Request $request);

    /**
     * Изменение товара
     * @throws Exception
     */
    public function modifyProduct(Request $request, $id);

    /**
     * Получение списка товаров
     *
     * @param Request $request
     * @return mixed
     */
    public function getProductsList(Request $request);

    /**
     * Получение детализации товара
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getProductDetail(Request $request, $id);

    /**
     * Удаление товара
     *
     * @param Request $request
     * @return mixed
     */
    public function removeProducts(Request $request);

    /**
     * Обновление товара
     *
     * @param Request $request
     * @return mixed
     */
    public function updateProducts(Request $request);

}
