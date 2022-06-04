<?php

namespace App\Exceptions\Product;

use App\Constants\Errors\ProductsErrors;
use Exception;

class ProductException extends Exception
{
    const MESSAGES = [
        ProductsErrors::NOT_FOR_SALE => 'Товар не продается',
        ProductsErrors::NOT_FOUND => 'Товар не найден',
        ProductsErrors::ALREADY_EXISTS => 'Товар был добавлен ранее',
        ProductsErrors::NOT_FILLED_REQUIRED_CHARACTERISTICS => 'Необходимо заполнить обязательные характеристики',
        ProductsErrors::CHARACTERISTIC_NOT_FOUND => 'Характеристика не найдена',
        ProductsErrors::CAN_NOT_SEND => 'Ошибка при отправке данных в Ozon',
        ProductsErrors::CHARACTERISTIC_OPTION_NOT_FOUND => 'У характеристики не указаны возможные значения',
        ProductsErrors::WRONG_SKU_FORMAT => 'Неверный формат введенных данных',
        ProductsErrors::TOO_MANY_SKUS => 'Допускается ввод не более 10 товаров',
        ProductsErrors::ALREADY_ADDED_BY_ANOTHER_USER => 'Товар добавлен в систему другим пользователем',
        ProductsErrors::ADDED_WITHOUT_DATA => 'Добавлен в систему без загрузки данных',
        ProductsErrors::EMPTY_RESULT_IN_RESPONSE => 'Отсутствует ответ от Ozon',
        ProductsErrors::NOT_FOUND_IN_OZON => 'Товар не найден в Ozon',
    ];

    /**
     * ProductException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(self::MESSAGES[$code], $code);
    }
}
