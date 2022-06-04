<?php

namespace Tests\Api\v1\Account;

use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

class WildberriesSetAccessTest extends AbstractSetAccessTest
{
    use WithFaker;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userParams = [
            'client_api_key' => Str::random(25),
            'client_id' => Str::random(25),
            'platform_id' => 3,
            'title' => $this->faker->word(),
        ];

        $this->http = new TestHttp();
    }
}
