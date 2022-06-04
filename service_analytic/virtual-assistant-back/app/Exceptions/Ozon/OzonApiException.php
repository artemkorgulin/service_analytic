<?php


namespace App\Exceptions\Ozon;

use App\Constants\Errors\OzonApiErrors;
use RuntimeException;

class OzonApiException extends RuntimeException
{
    const MESSAGES = [
        400 => 'Неверный параметр',
        404 => 'Ошибка получения данных о товаре. Проверьте, есть ли в вашей учётной записи Озон данный товар',
        429 => 'Превышен лимит запросов Ozon',
        OzonApiErrors::EMPTY_API_KEY => 'Установите API ключ для сбора данных на странице "Настройки"',
    ];

    const DEFAULT = 'Неизвестная ошибка. Попробуйте позднее.';

    /**
     * OzonApiException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(static::MESSAGES[$code] ?? static::DEFAULT, $code);
    }
}
