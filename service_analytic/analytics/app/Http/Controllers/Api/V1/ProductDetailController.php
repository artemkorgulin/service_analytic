<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Repositories\V1\CardProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CardProduct\CardProductDetailRequest;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class ProductDetailController extends Controller
{
    private CardProductRepositoryInterface $cardProductRepository;

    public function __construct(
        CardProductRepositoryInterface $cardProductRepository
    ) {
        $this->cardProductRepository = $cardProductRepository;
    }

    public function getStatistic($vendorCode, CardProductDetailRequest $request)
    {
        try {
            $product = $this->cardProductRepository->getDetailStatistic($vendorCode, $request);
            return response()->api_success([$product]);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    public function getRecommendation($vendorCode)
    {
        $recommendations = null;
        $cardProduct = $this->cardProductRepository->firstByVendorCode($vendorCode);
        if (isset($cardProduct)) {
            $recommendations = $this->cardProductRepository->getRecommendations($vendorCode)->first();
            $recommendations->product_rating = $cardProduct->grade;
            $recommendations->product_comments = $cardProduct->comments;
        }

        return response()->api_success([$recommendations]);
    }

}
