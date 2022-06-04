<?php

namespace App\Helpers;

class PaymentHelper
{
    /**
     * @param  array  $services
     * @return array
     */
    public static function prepareServices(array $services): array
    {
        $result = [];

        foreach ($services as $service) {
            $result[$service['alias']] = $service;
        }

        return $result;
    }

    /**
     * @param  array  $services
     * @param  string  $serviceAlias
     * @return bool
     */
    public static function serviceIsset(array $services, string $serviceAlias): bool
    {
        return array_key_exists($serviceAlias, self::prepareServices($services));
    }
}
