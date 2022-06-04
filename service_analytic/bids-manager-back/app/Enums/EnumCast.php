<?php

namespace App\Enums;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MyCLabs\Enum\Enum;

/**
 * @author https://github.com/akorgulin
 * @see https://github.com/akorgulin/eloquent-enum-cast/
 */
trait EnumCast
{

    protected static $strictMode = true;


    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {

            /**
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  string  $key
             * @param  array  $value
             * @param  array  $attributes
             */
            public function get($model, $key, $value, $attributes): Enum
            {
                $enum = $model->getCasts()[$key];

                return $enum::from($value);
            }


            /**
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  string  $key
             * @param  array  $value
             * @param  array  $attributes
             */
            public function set($model, $key, $value, $attributes)
            {
                $enum = $model->getCasts()[$key];

                if ($value instanceof $enum) {
                    return $value->getValue();
                }

                $enum::assertValidValue($value);

                return $value;
            }
        };
    }


    /**
     * Return key for value
     *
     * @param  mixed  $value
     *
     * @psalm-param mixed $value
     * @psalm-pure
     * @return string|false
     */
    public static function search($value)
    {
        return array_search($value, static::toArray(), static::$strictMode);
    }
}
