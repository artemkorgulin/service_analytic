<?php

namespace App\Http\Controllers;

use App\Http\Requests\EscrowAbuseRequest;
use App\Http\Requests\EscrowOzonRequest;
use App\Http\Requests\EscrowWildberriesRequest;
use App\Models\OzProduct;
use App\Models\WbProduct;
use App\Services\Escrow\EscrowAbuseService;
use App\Services\Escrow\EscrowPdfService;
use App\Services\Escrow\EscrowService;
use App\Services\Escrow\IregService;
use Illuminate\Http\JsonResponse;

class EscrowController extends Controller
{
    /**
     * Generating certificate and escrow images for ozon
     * @param EscrowOzonRequest $escrowRequest
     * @param EscrowService $escrowService
     * @param IregService $iregService
     * @return mixed
     */
    public function escrowOzon(EscrowOzonRequest $escrowRequest, EscrowService $escrowService, IregService $iregService): mixed
    {
        $array = $escrowService->processOzonEscrow($escrowRequest, $iregService, $escrowRequest->product_id);
        if (isset($array['error'])) {
            return response()->api_fail($array);
        }
        return response()->api_success($array, 200);
    }

    /**
     * Generating certificate and escrow images for wb
     * @param EscrowWildberriesRequest $escrowRequest
     * @param EscrowService $escrowService
     * @param IregService $iregService
     * @return mixed
     */
    public function escrowWildberries
    (
        EscrowWildberriesRequest $escrowRequest,
        EscrowService $escrowService,
        IregService $iregService,
    )
    {

        $array = $escrowService->processWbEscrow($escrowRequest, $iregService, $escrowRequest);
        if (isset($array['error'])) {
            return response()->api_fail($array);
        }
        return response()->api_success($array, 200);
    }

    /**
     * Get abuse pdf for ozon
     *
     * @param EscrowAbuseRequest $escrowAbuseRequest
     * @param EscrowService $escrowService
     * @param EscrowAbuseService $escrowAbuseService
     * @return mixed
     */
    public function getOzonAbusePdf(
        EscrowAbuseRequest $escrowAbuseRequest,
        EscrowService $escrowService,
        EscrowAbuseService $escrowAbuseService
    ): mixed
    {
        return $escrowService->generateAbusePdf(
            $escrowAbuseRequest,
            $escrowAbuseService,
            OzProduct::class
        );
    }

    /**
     * Get abuse pdf for WB
     *
     * @param EscrowAbuseRequest $escrowAbuseRequest
     * @param EscrowService $escrowService
     * @param EscrowAbuseService $escrowAbuseService
     * @return mixed
     */
    public function getWbAbusePdf(
        EscrowAbuseRequest $escrowAbuseRequest,
        EscrowService $escrowService,
        EscrowAbuseService $escrowAbuseService
    ): mixed
    {
        return $escrowService->generateAbusePdf(
            $escrowAbuseRequest,
            $escrowAbuseService,
            WbProduct::class
        );
    }

    /**
     * Get escrow remain and total limits
     *
     * @param EscrowService $escrowService
     * @return JsonResponse
     */
    public function getLimits(EscrowService $escrowService): JsonResponse
    {
        return $escrowService->getEscrowLimits();
    }

    /**
     * Get escrow remain and limits product
     *
     * @param EscrowService $escrowService
     * @param $id
     * @return JsonResponse
     */
    public function getLimitsProduct(EscrowService $escrowService, $id): JsonResponse
    {
        $data_success = $escrowService->getEscrowLimitsProduct($id);
        return response()->api_success($data_success);
    }


    /**
     * Get escrow devide and limits product
     *
     * @param EscrowService $escrowService
     * @param $id
     * @return JsonResponse
     */
    public function getEscrowDevideProduct(EscrowService $escrowService, $id): JsonResponse
    {
        $data_success = $escrowService->getEscrowDevideToProduct($id);
        return response()->api_success($data_success);
    }
}
