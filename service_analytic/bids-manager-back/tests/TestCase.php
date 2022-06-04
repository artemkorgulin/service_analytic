<?php

namespace Tests;

use Illuminate\Support\Arr;
use Tests\Traits\UserForTest;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutEvents;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithoutEvents, UserForTest;

    public $dataset = [];

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->dataset = self::getDataset();

        parent::__construct($name, $data, $dataName);
    }

    public function getDataKey($key)
    {
        if (!Arr::has($this->dataset, $key)) {
            return null;
        }

        return Arr::get($this->dataset, $key);
    }
}
