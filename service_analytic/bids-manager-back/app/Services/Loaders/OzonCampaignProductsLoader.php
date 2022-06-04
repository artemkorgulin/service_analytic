<?php

namespace App\Services\Loaders;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Constants\Constants;
use App\Services\DatabaseService;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use function Swoole\Coroutine\run;
use co;

class OzonCampaignProductsLoader extends LoaderService
{
    /**
     * Загрузить данные о товарах в рекламных кампаниях с Озона
     *
     * @param array $params
     */
    protected function start(array $params = [])
    {
        echo date('Y-m-d H:i:s').': Start campaign products loading'.PHP_EOL;

        $campaignIds = $params['campaignIds'] ?? null;

        // Для всех активных аккаунтов
        $accountChunks = UserService::getAdmAccounts()->chunk(50);

        foreach ($accountChunks as $accounts) {
            run(function () use ($accounts, $campaignIds) {
                foreach ($accounts as $account) {
                    go(function () use ($account, $campaignIds) {
                        // Коннектимся к Озону
                        $ozonConnection = OzonPerformanceService::connectRepeat($account, $this->client,
                            $this->cacheRepository);

                        if (!$ozonConnection) {
                            co::cancel(co::getCid());
                        }

                        $statusesIds = CampaignStatus::query()
                            ->whereNotIn('code', [CampaignStatus::DRAFT, CampaignStatus::ARCHIVED])
                            ->pluck('id');

                        // Собираем из базы список кампаний текущего аккаунта
                        $campaigns = Campaign::query()
                            ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
                            ->select('campaigns.id', 'campaigns.ozon_id', 'campaigns.campaign_status_id')
                            ->where('account_id', $account->id)
                            ->whereNotNull('ozon_id')
                            ->when($campaignIds, function (Builder $query) use ($campaignIds) {
                                $query->whereIn('campaigns.id', $campaignIds);
                            })
                            ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
                            ->whereIn('campaign_status_id', $statusesIds)
                            ->orderBy('campaigns.id', 'desc')
                            ->get();

                        foreach ($campaigns as $campaign) {
                            $result = $ozonConnection->getCampaignProducts($campaign->ozon_id);
                            if (!$result) {
                                echo 'Get Campaign '.$campaign->id.' Product List Error'.PHP_EOL;
                                $lastError = $ozonConnection::getLastError();

                                // Если ошибка 403 - мы разлогинились, дальше пробовать бессмысленно
                                if ($lastError['code'] === 403) {
                                    continue;
                                }

                                continue;
                            }

                            DatabaseService::saveCampaignProducts($result->products, $campaign, $account);
                        }
                    });
                }
            });
        }

        echo date('Y-m-d H:i:s').': Campaign products was loaded'."\r\n";
    }
}
