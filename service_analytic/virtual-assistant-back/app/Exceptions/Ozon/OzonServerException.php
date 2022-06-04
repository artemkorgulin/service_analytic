<?php

namespace App\Exceptions\Ozon;

use Exception;

class OzonServerException extends Exception
{
    const MESSAGES = [
        403 => 'Невереный Ozon API Key',
        500 => 'Внутренняя ошибка Ozon',
    ];

    /**
     * OzonServerException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(static::MESSAGES[$code] ?? static::MESSAGES[500], $code);
    }
}
