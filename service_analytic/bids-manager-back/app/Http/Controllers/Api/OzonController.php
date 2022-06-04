<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OzonController extends Controller
{
    /**
     * Get campaigns list
     * @param Request $request
     * @return array|JsonResponse
     */
    public function getCampaignsList(Request $request)
    {
        $account = UserService::getCurrentAccount();

        $ozonConnection = OzonPerformanceService::connectRepeat($account);
        if( !$ozonConnection ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [OzonPerformanceService::getLastError()],
                ]
            );
        }

        $res = $ozonConnection->getClientCampaigns();
        if( $res ) {
            return $res->list;
        }

        return response()->json([
            'success' => false,
        ]);
    }

    /**
     * Check request status of report readiness
     *
     * @param Request $request
     *
     * @return JsonResponse|string
     */
    public function checkRequestStatus(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string'
        ]);
        $uuid = $request->get('uuid');
        $account = UserService::getCurrentAccount();

        $ozonConnection = OzonPerformanceService::connectRepeat($account);
        if( !$ozonConnection ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [OzonPerformanceService::getLastError()],
                ]
            );
        }

        $res = $ozonConnection->getClientStatisticsReportState($uuid);
        if( $res ) {
            return $res->state;
        }

        return response()->json([
            'success' => false,
        ]);
    }
}
