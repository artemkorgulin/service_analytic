<?php


namespace App\Services;

use App\Jobs\ParserRabbit;

class ParserRabbitService
{
    private array $config;

    /**
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string|array  $messages
     * @return void
     */
    public function sendMessage(string|array $messages)
    {
        ParserRabbit::dispatch($messages, $this->config);
    }
}
