<?php

namespace App\Helpers;

use JetBrains\PhpStorm\ArrayShape;

class WildberriesHelper
{

    const IMG_URL = 'https://images.wbstatic.net/';
    const WB_URL = 'https://www.wildberries.ru/';

    #[ArrayShape(['small' => "string", 'big' => "string", 'middle' => "string"])]
    public static function generateWbImagesUrl(string $sku): array
    {
        $part = substr($sku, 0, strlen($sku) - 4).'0000';
        return array(
            'small' => self::IMG_URL."tm/new/$part/$sku-1.jpg",
            'big' => self::IMG_URL."big/new/$part/$sku-1.jpg",
            'middle' => self::IMG_URL."c252x336/new/$part/$sku-1.jpg"
        );
    }

    public static function generateProductUrl(string $sku): string
    {
        return self::WB_URL."catalog/$sku/detail.aspx";
    }
}