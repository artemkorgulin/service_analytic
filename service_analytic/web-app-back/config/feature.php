<?php

return [
    # Возможность регистрироваться и входить на сайт, используя номер телефона
    'phone-login' => env('FEATURE_PHONE_LOGIN', false),
    'phone-login-throttle' => env('FEATURE_PHONE_LOGIN_THROTTLE', false),
];
