<?php

namespace App\Http\Controllers\Api\V1\Action;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Repositories\V1\Action\WbHistoryProductRepository;
use App\Repositories\V1\Action\OzHistoryProductRepository;
use App\Services\CallsForActionService;
use App\Services\UserService;
use App\Contracts\ActionParams;
use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;

class HistoryProductController extends Controller
{
    /**
     * @param  CallsForActionService  $actionService
     *
     * @return mixed
     * @throws \Exception
     */
    public function getData(CallsForActionService $actionService): mixed
    {
        $historyRepository = $this->getRepository();

        return response()->api_success($actionService->getData($historyRepository));
    }

    /**
     * @param  CallsForActionService  $actionService
     * @param  int  $vendorCode
     *
     * @return mixed
     */
    public function getDiagramData(
        CallsForActionService $actionService,
        int $vendorCode
    ): mixed {
        $historyRepository = $this->getRepository();

        return response()->api_success($actionService->getDiagramData($historyRepository, $vendorCode));
    }

    /**
     * @return OzHistoryProductRepository|WbHistoryProductRepository
     */
    public function getRepository(): OzHistoryProductRepository|WbHistoryProductRepository
    {
        $account = UserService::getAccountActive();

        if (!$account || !isset($account['platform_id'])) {
            throw new \Exception('Нет аккаунта в запросе или не выбран аккаунт: ' . json_encode($account));
        }

        $platformId = (int) $account['platform_id'];

        if ($platformId === ActionParams::PLATFORM_ID_WB) {
            return new WbHistoryProductRepository();
        } elseif ($platformId === ActionParams::PLATFORM_ID_OZOM) {
            return new OzHistoryProductRepository();
        } else {
            return response()->api_fail('Выбран неверный маркетплейс.', [], 422,
                AuthErrors::EMPTY_API_TOKEN);
        }
    }
}
