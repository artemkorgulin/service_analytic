<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsCreateRequest;
use App\Http\Requests\NewsUpdateRequest;
use App\Models\News;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    /**
     * Получить список новостей.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $news = News::all();

            return response()->api_success($news);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Создать новость.
     *
     * @param  NewsCreateRequest  $request
     * @return JsonResponse
     */
    public function store(NewsCreateRequest $request): JsonResponse
    {
        try {
            $news = News::create($request->toArray());

            return response()->api_success($news);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Обновить новость.
     *
     * @param  News  $news
     * @param  NewsUpdateRequest  $request
     * @return JsonResponse
     */
    public function update(News $news, NewsUpdateRequest $request): JsonResponse
    {
        try {
            $data = $request->toArray();
            unset($data['author_id']);
            $news->fill($data)->save();

            return response()->api_success($news);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Удалить новость.
     *
     * @param  News  $news
     * @return JsonResponse
     */
    public function destroy(News $news): JsonResponse
    {
        try {
            $news->delete();

            return response()->api_success($news);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Посмотреть новость.
     *
     * @param  News  $news
     * @return JsonResponse
     */
    public function show(News $news): JsonResponse
    {
        try {
            return response()->api_success($news);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
