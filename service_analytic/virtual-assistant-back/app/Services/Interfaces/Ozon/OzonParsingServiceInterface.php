<?php

namespace App\Services\Interfaces\Ozon;

interface OzonParsingServiceInterface
{
    public function parseOzonCategory($url, $proxy);
    public function updateProductCategory($id, $category);
    public function getRandomProxy();
    public function createCategoryForProduct($product);
}
