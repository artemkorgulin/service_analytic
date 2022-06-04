<?php

namespace App\Constants\References;

/**
 * Class ProductStatusesConstants
 * Статусы товаров при модерации в Озон
 * @package App\Constants\References
 */
class ProductStatusesConstants
{
    public const ERROR_CODE = 'error';
    public const ERROR_NAME = 'Ошибка';

    public const VERIFIED_CODE = 'verified';
    public const VERIFIED_NAME = 'Проверен';

    public const MODERATION_CODE = 'moderation';
    public const MODERATION_NAME = 'На модерации';

    public const CREATED_CODE = 'created';
    public const CREATED_NAME = 'Создан (локально)';

}
