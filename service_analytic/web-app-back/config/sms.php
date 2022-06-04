<?php

/**
 * Конфигурация для отправки sms
 */
return [
    'class' => env('SMS_PROVIDER_CLASS', App\Services\SMS\LogService::class)
];
