<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * @param  PositionService  $positionService
     */
    public function __construct(private PositionService $positionService)
    {
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopPositionProducts(Request $request)
    {
        $userId = $request->input('user')['id'];
        $limit = (int) $request->input('limit');

        $topWb = $this->positionService->getTopWb($userId, $limit);

        $topOz = $this->positionService->getTopOzon($userId, $limit);

        $result = array_merge($topWb, $topOz);

        usort($result, function (array $a, array $b) {
            return ($a['position'] <=> $b['position']);
        });

        $result = array_slice($result, 0, $limit);

        return response()->json($result);
    }
}
