<?php

namespace App\Helpers\Mocks\Api;

use App\Contracts\Api\OzonApiInterface;

class OzonApiMock implements OzonApiInterface
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
     * @see OzonApiInterface::validateAccessData()
     */
    public function validateAccessData(): bool
    {
        return true;
    }
}
