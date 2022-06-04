<?php

namespace App\Http\Controllers;

use App\Constants\PlatformConstants;
use App\Jobs\CreateProductsForOzonAccount;
use App\Jobs\CreateProductsForWildberriesAccount;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    /**
     * Новый аккаунт
     */
    public function newAccount(Request $request) {
        try {
            $user = $request->user ?? null;
            $accountParams = [
                'id' => $request->account_id,
                'client_id' => $request->client_id,
                'client_api_key' => $request->client_api_key
            ];
            $platformTitle = '';
            $platformId = (int) $request->platform_id;

            if (empty($user) || empty($accountParams['id']) || empty($accountParams['client_id']) || empty($accountParams['client_api_key'])) {
                $errorMessage = 'Недостаточно данных для выполнения загрузки товаров. Пользователь или аккаунт не указаны.';
                Log::error($errorMessage);

                return response()->api_fail($errorMessage, [], 422);
            }

            if ($platformId === PlatformConstants::WILDBERRIES_PLATFORM_ID) {
                $platformTitle = 'Wildberries';
                try {
                    CreateProductsForWildberriesAccount::dispatch($user, $accountParams)->onQueue('default_long');
                } catch (\Exception $exception) {
                    report($exception);
                    ExceptionHandlerHelper::logFail($exception);
                }
            }

            if ($platformId === PlatformConstants::OZON_PLATFORM_ID) {
                $platformTitle = 'Ozon';
                try {
                    CreateProductsForOzonAccount::dispatch($user, $accountParams)->onQueue('default_long');
                } catch (\Exception $exception) {
                    report($exception);
                    ExceptionHandlerHelper::logFail($exception);
                }
            }

            $message = __('Product for :account is loading', ['account' => $accountParams['client_id']]);

            Log::info($message);
            return response()->api_success(['result' => $message], 200);
        } catch (\Exception $exception) {
            report($exception);
            return response()->api_fail($exception->getMessage(), [], 500);
        }
    }
}
