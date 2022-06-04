<?php
namespace App\Contracts;

use GuzzleHttp\Psr7\Response;

/**
 * Интерфейс сервиса, осуществляющего отправку sms человеку
 */
interface SMSInterface
{
    /**
     * Метод отправки сообщения пользователю
     */
    public function send(string $phone, string $msg): Response;
}
