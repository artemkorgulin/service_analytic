<?php

namespace App\Services\Analytics\Interfaces;

interface WbAnalyticsServiceInterface
{
    function getProductsRating($productIds): array|null;
}
