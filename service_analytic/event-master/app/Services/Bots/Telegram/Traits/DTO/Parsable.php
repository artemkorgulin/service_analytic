<?php

namespace App\Services\Bots\Telegram\Traits\DTO;

trait Parsable
{

    /**
     * @param  string  $paramsJson
     *
     * @return static
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function make(string $paramsJson): static
    {
        return new static(self::parse($paramsJson));
    }


    /**
     * @param  string  $params
     *
     * @return mixed
     */
    private static function parse(string $params): mixed
    {
        return json_decode($params, true);
    }
}
