<?php

namespace App\Services\Loaders;

use App\DataTransferObjects\AccountDTO;
use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignListDTO;
use App\Events\Ozon\AccountCampaignsLoadingFinished;
use App\Services\DatabaseService;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Illuminate\Support\Collection;

class OzonCampaignsLoader extends LoaderService
{

    /**
     * Обновить данные о кампаниях с Озона
     *
     * @param  array  $params
     */
    protected function start(array $params = [])
    {
        echo date('Y-m-d H:i:s').': Start campaigns loading'.PHP_EOL;

        // Для всех активных аккаунтов
        $chunkCounter = 0;
        UserService::chunkAllAdmAccountsGet(function (Collection $accounts) use ($chunkCounter) {
            printf('Processing %d chunk with %d accounts'.PHP_EOL, $chunkCounter++, $accounts->count());
            foreach ($accounts as $account) {
                // Коннектимся к Озону
                $ozonConnection = OzonPerformanceService::connectRepeat($account, $this->client,
                    $this->cacheRepository);
                if (!$ozonConnection) {
                    continue;
                }

                $this->outputAccountInfo($account);

                // Получаем все кампании
                /** @var CampaignListDTO $campaignList */
                $campaignList = $ozonConnection->getClientCampaigns();
                //todo: there may be no campaigns in the provided account
                if (!$campaignList) {
                    echo 'Get Campaigns List Error'.PHP_EOL;
                    var_dump($ozonConnection::getLastError());
                    continue;
                }

                // Обновляем локально базу
                DatabaseService::saveCampaignList($campaignList, $account->id);
                AccountCampaignsLoadingFinished::dispatch($campaignList, $account->id);
            }
        });


        echo date('Y-m-d H:i:s').': Campaigns loading finished'.PHP_EOL;
    }
}
