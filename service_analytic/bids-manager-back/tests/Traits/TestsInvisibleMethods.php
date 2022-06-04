<?php

namespace Tests\Traits;

use ReflectionClass;

trait TestsInvisibleMethods
{

    /**
     * Call protected or private method
     *
     * @param  array  $object
     * @param  mixed  $arguments
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function callMethod(array $callable, mixed $arguments = []): mixed
    {
        if (!is_array($arguments)) {
            $arguments = (array) $arguments;
        }

        list($object, $method) = $callable;
        $reflectionClass = new ReflectionClass($object);
        $method          = $reflectionClass->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
