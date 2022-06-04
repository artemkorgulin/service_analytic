<?php


namespace App\Exceptions\Wb;

use RuntimeException;

class WbApiException extends RuntimeException
{
    const MESSAGES = [
        0 => 'Не задан параметр wb_product_name',
        1 => 'Не задан параметр wb_product_name'
    ];

    const DEFAULT = 'Неизвестная ошибка. Попробуйте позднее.';

    /**
     * WbApiException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(static::MESSAGES[$code] ?? static::DEFAULT, $code);
    }
}
