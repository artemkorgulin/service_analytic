<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class PhoneHelper
{
    /**
     * Вернуть только числа в строке. Ну там телефон от лишних символов почистить
     */
    public static function normalizePhone($string)
    {
        return preg_replace('/\D/', '', $string);
    }

    /**
     * Токен для подтверждения телефона
     */
    public static function generateVerificationToken()
    {
        return Str::random(6);
    }

    /**
     * Отформатировать телефон красивее для вывода
     */
    public static function format($phone)
    {
        // взято с фронта assets/js/utils/numbers.utils.js
        return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', self::normalizePhone($phone));
    }
}
