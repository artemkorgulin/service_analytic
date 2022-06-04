<?php

namespace App\Helpers\Mocks\Api;

use App\Contracts\Api\WildberriesApiInterface;

class WildberriesApiMock implements WildberriesApiInterface
{
    /**
     * @param $clientId
     * @param $apiKey
     */
    public function __construct(private $clientId, private $apiKey)
    {
        //
    }

    /**
     * @inheritDoc
     * @see WildberriesApiInterface::validateAccessData()
     */
    public function validateAccessData(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @see WildberriesApiInterface::validateAccessDataForClientId()
     */
    public function validateAccessDataForClientId(): bool
    {
        return true;
    }
}
