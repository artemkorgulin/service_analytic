<?php

namespace App\Exceptions;

use AnalyticPlatform\LaravelHelpers\Constants\Errors\ValidationErrors;
use App\Exceptions\Ozon\OzonApiException;
use App\Exceptions\Ozon\OzonServerException;
use App\Exceptions\Product\ProductException;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\SentryExceptionHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        ProductException::class,
        OzonServerException::class,
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
        try {
            SentryExceptionHelper::sendExceptionDataInSentry($exception);
        } catch (\Exception $exceptionSentry) {
            Log::critical(
                'Ошибка отправки данных в sentry.',
                ['sentry_exception' => $exceptionSentry, 'app_exception' => $exception]);
        }

        parent::report($exception);
    }

    public function register()
    {
        SentryExceptionHelper::registerSentry($this);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof OzonApiException || $exception instanceof ProductException || $exception instanceof OzonServerException) {
            Log::error('Ошибка при отправке в озон: '.$exception->getMessage(), $exception->getTrace());

            return response()->api_fail(
                'Ошибка при отправке в Озон',
                [],
                422,
                $exception->getCode()
            );
        }

        if ($exception instanceof ValidationException && ($request->ajax() || $request->wantsJson())) {
            $advanced_data = [];
            $content = json_decode($request->getContent(), true);

            foreach ($exception->errors() as $field_name => $field_errors) {
                if (preg_match('/^characteristics\.(\d+)\.value$/', $field_name, $match)) {
                    $index = end($match);
                    $advanced_data[][$content['characteristics'][$index]['id']] = $field_errors;
                } else {
                    $advanced_data[$field_name] = $field_errors[0];
                }
            }

            return response()->api_fail('Ошибка валидации', $advanced_data, 422, ValidationErrors::VALIDATION_ERROR);
        }

        return ExceptionHandlerHelper::run($exception, $request, $this);
    }
}
