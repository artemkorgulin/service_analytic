<?php

use App\Http\Controllers\Api\v1\NewsController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\NotificationSchemaController;
use App\Http\Controllers\Api\v1\NotificationSubtypeController;
use App\Http\Controllers\Api\v1\NotificationTemplateController;
use App\Http\Controllers\Api\v1\NotificationTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::post('/{token}/updates', [\App\Http\Controllers\TelegramBotController::class, 'handleRequest'])
        ->middleware('telegram.token')
        ->withoutMiddleware('api');
    Route::get('/telegram-bot-start-link', [\App\Http\Controllers\TelegramBotController::class, 'getBotStartLink']);
    Route::apiResource('/notifications', NotificationController::class);

    Route::get('/notification_schemas', [NotificationSchemaController::class, 'index']);
    Route::post('/notification_schemas', [NotificationSchemaController::class, 'store']);

    Route::get('/notification_types', [NotificationTypeController::class, 'index']);
    Route::post('/notification_types', [NotificationTypeController::class, 'store']);
    Route::get('/notification_subtypes', [NotificationSubtypeController::class, 'index']);
    Route::post('/notification_subtypes', [NotificationSubtypeController::class, 'store']);
    Route::apiResource('/notification_templates', NotificationTemplateController::class);

    Route::apiResource('/news', NewsController::class);

    Route::post('/notification_make_read', [NotificationController::class, 'makeRead']);
});
