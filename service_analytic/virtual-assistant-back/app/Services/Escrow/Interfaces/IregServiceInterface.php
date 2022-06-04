<?php

namespace App\Services\Escrow\Interfaces;

interface IregServiceInterface
{
    function fileStore($filePath, $product, $escrowRequest);
}
