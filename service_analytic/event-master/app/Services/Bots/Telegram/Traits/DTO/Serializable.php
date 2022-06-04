<?php

namespace App\Services\Bots\Telegram\Traits\DTO;

/**
 * @method toArray()
 */
trait Serializable
{
    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return json_encode($this->toArray());
    }
}
