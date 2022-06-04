<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Repositories\V1\CardProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CardProduct\CardProductRatingRequest;

class RatingController extends Controller
{


    public function __construct(
        private CardProductRepositoryInterface $cardProductRepository
    )
    {
    }


    public function getRatingsList(CardProductRatingRequest $request)
    {
        $ratings = $this->cardProductRepository->getRatingList($request['vendor_code'])
            ->mapWithKeys(function ($item) {
                return [$item['vendor_code'] => $item['grade']];
            });
        return response()->api_success([
            $ratings
        ]);
    }
}
