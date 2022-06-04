<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserParamsRequest;
use App\Models\Analytica\UserParams;
use App\Services\UserParamsService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;

class UserParamController extends Controller
{
    /**
     * @param  UserParamsService  $userParamsService
     */
    public function __construct(private UserParamsService $userParamsService)
    {
    }

    /**
     * @param  UserParamsRequest  $request
     * @return mixed
     */
    public function index(UserParamsRequest $request)
    {
        try {
            $userId = $request->input('user')['id'];
            $url = $request->input('url');
            $userParam = UserParams::where('user_id', $userId)->where('url', $url)->first();

            $result = json_decode($userParam?->params);

            if (!$userParam) {
                $result = $this->userParamsService->getDefaultParams($request->input('url'), $userId);
            }

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
