<?php

namespace App\Exceptions;

use App\Exceptions\Telegram\TelegramException;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\SentryExceptionHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Telegram\Bot\Exceptions\TelegramSDKException;
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
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof TelegramException || $exception instanceof TelegramSDKException) {
            if (method_exists($exception, 'sendMessage')) {
                $exception->sendMessage();
            }

            ExceptionHandlerHelper::logFail($exception);

            return response('ok', 200);
        }

        return ExceptionHandlerHelper::run($exception, $request, $this);
    }
}
