<?php

namespace App\Exceptions\YooKassa;

use Exception;

class YooKassaApiException extends Exception
{
    const MESSAGES = [
        0 => 'Не удалось установить соединение с YooKassa',
        202 => 'Ваш запрос обрабатывается',
        401 => 'Неверный shopId или secret key.',
        429 => 'Превышен лимит запросов YooKassa. Попробуйте позднее.',
    ];

    const DEFAULT = 'Не удалось создать платеж в YooKassa';

    /**
     * YooKassaApiException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(self::MESSAGES[$code] ?? self::DEFAULT, $code);
    }
}
