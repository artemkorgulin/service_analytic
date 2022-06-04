<?php

namespace App\Helpers\Controller;

use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;

class ExceptionControllerHelper
{

    /**
     * @param $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function catchException($exception)
    {
        report($exception);
        return  ExceptionHandlerHelper::logAndSendFailResponse($exception);
    }
}
