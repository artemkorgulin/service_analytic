<?php
return [
    'endpoint' => 'https://sms.edna.ru/connector_sme/api/smsOutMessage',
    'api_key' => env('EDNA_API_KEY'),
    'subject' => env('EDNA_SUBJECT', 'edna.test'),
];
