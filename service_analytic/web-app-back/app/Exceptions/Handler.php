<?php

namespace App\Exceptions;

use AnalyticPlatform\LaravelHelpers\Constants\Errors\ValidationErrors;
use App\Exceptions\YooKassa\YooKassaApiException;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\SentryExceptionHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        SentryExceptionHelper::sendExceptionDataInSentry($exception);
        parent::report($exception);
    }

    public function register()
    {
        SentryExceptionHelper::registerSentry($this);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof YooKassaApiException) {
            Log::error('Ошибка при отправке запроса: ' . $exception->getMessage(), $exception->getTrace());
            return response()->api_fail(
                $exception->getMessage(),
                [],
                422,
                $exception->getCode()
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        return ExceptionHandlerHelper::run($exception, $request, $this);
    }
}
