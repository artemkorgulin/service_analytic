<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Response;
use App\Contracts\SMSInterface;

/**
 * Сервис что делает вид, будто-бы отправку sms совершил, а на самом деле - записывает её в лог.
 */
class LogService implements SMSInterface
{
    /**
     * Отправить текст по номеру телефона
     */
    public function send(string $phone, string $msg): Response
    {
        Log::info('Отправка sms человеку', compact('phone', 'msg'));
        return new Response(
            200,
            [],
            sprintf('Ответ будто-бы sms с текстом "%s" было отправлено на номер "%s"', $msg, $phone));
    }
}
