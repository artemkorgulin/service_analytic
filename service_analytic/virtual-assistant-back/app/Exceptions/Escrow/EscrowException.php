<?php

namespace App\Exceptions\Escrow;

use App\Constants\Errors\EscrowErrors;
use Exception;

class EscrowException extends Exception
{
    const MESSAGES = [
        EscrowErrors::NO_IMAGES => 'Не удалось получить изображения товара',
        EscrowErrors::NO_HASHES => 'Не удалось загрузить изображения, возможно адрес ссылок на картинки недействителен, либо попробуйте позднее',
        EscrowErrors::NO_ESCROW => 'Не удалось получить шифры изображений для депонирования',
        EscrowErrors::NO_CERTIFICATES => 'Не удалось получить сертификаты, попробуйте выполнить депонирование позднее',
    ];

    /**
     * EscrowException constructor.
     * @param int $code
     */
    public function __construct($code = 0)
    {
        parent::__construct(self::MESSAGES[$code], $code);
    }
}
