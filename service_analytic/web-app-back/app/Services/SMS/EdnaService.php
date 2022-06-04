<?php

namespace App\Services\SMS;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleLogMiddleware\LogMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Contracts\SMSInterface;
use App\Services\PhoneService;

class EdnaService implements SMSInterface
{
    private $client;

    public function __construct()
    {
        $stack = HandlerStack::create();
        $stack->push(new LogMiddleware(Log::channel('guzzle_request')));

        $this->client = new Client([
            'headers' => $this->headers(),
            'handler' => $stack
        ]);
    }

    /**
     * Отправить текст по номеру телефона
     */
    public function send(string $phone, string $msg): Response
    {
        return $this->client->request(
            'POST',
            config('edna.endpoint'),
            [
                'json' => [
                    "id" => $this->randomId(),
                    "subject" => config('edna.subject'),
                    "address" => $phone,
                    "priority" => "normal",
                    "validityPeriodSeconds" => PhoneService::PHONE_TOKEN_LIFETIME,
                    "serviceOnly" => false,
                    "contentType" => "text",
                    "content" => $msg,
                ]
            ]
        );
    }

    /**
     * Случайный идентификатор для sms
     */
    private function randomId()
    {
        return Str::random(20);
    }

    /**
     * Заголовки для запроса к сервису edna
     */
    private function headers()
    {
        return [
            'X-API-KEY' => config('edna.api_key')
        ];
    }
}
