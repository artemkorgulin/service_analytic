<?php

namespace App\Classes;

class DataHelper
{
    /**
     * Получить слово с нужным окончанием, в зависимости от числа.
     *
     * @param int $num
     * @param array $words
     * @return string
     */
    public static function num2word(int $num, array $words): string
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1:
            {
                return ($words[0]);
            }
            case 2:
            case 3:
            case 4:
            {
                return ($words[1]);
            }
            default:
            {
                return ($words[2]);
            }
        }
    }
}
