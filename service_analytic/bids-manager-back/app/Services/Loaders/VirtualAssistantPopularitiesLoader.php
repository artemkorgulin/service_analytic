<?php

namespace App\Services\Loaders;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Constants\Constants;
use App\Models\Keyword;
use App\Services\DatabaseService;
use App\Services\VirtualAssistantService;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VirtualAssistantPopularitiesLoader extends LoaderService
{
    /**
     * Загрузить популярность из Виртуального помощника
     *
     * @param array $params
     *
     * @return bool
     */
    protected function start($params = [])
    {
        echo date('Y-m-d H:i:s').': Start loading popularity'."\r\n";

        $campaignIds = $params['campaignIds'] ?? null;
        $dateFrom = $params['dateFrom'] ?? null;

        // Список кампаний, для которых отработает алгоритм
        $campaignsQuery = Campaign::query()
            ->join('campaign_types', 'campaign_types.id', '=', 'campaigns.type_id')
            ->join('campaign_page_types', 'campaign_page_types.id', '=', 'campaigns.page_type_id')
            ->join('campaign_statuses', 'campaign_statuses.id', '=', 'campaigns.campaign_status_id')
            ->when($campaignIds, function (Builder $query) use ($campaignIds) {
                $query->whereIn('campaigns.id', $campaignIds);
            })
            ->where('campaign_types.code', Constants::CAMPAIGN_SKU)
            ->where('campaign_page_types.name', Constants::CAMPAIGN_SEARCH)
            ->whereNotIn('campaign_statuses.code', [CampaignStatus::DRAFT, CampaignStatus::ARCHIVED])
            ->where(function (Builder $query) {
                $query->whereDate('last_vp_sync', '<', Carbon::today())
                    ->orWhereNull('last_vp_sync');
            })
            ->orderBy('campaigns.id', 'desc')
            ->select('campaigns.id', 'campaigns.vp_data_date')
            ->with('campaignProducts:id,campaign_id', 'campaignProducts.campaignKeywords:id,campaign_product_id,keyword_id');

        if( !$dateFrom ) {
            $lastPopularityDate = $campaignsQuery->min('vp_data_date');
            echo 'lastPopularityDate: '.$lastPopularityDate."\r\n";
            $dateFrom = Carbon::parse($lastPopularityDate)->addDay()->toDateString();
        }
        $dateTo = Carbon::yesterday()->toDateString();

        if( $dateFrom > Carbon::today()->subDays(3)->toDateString() ) {
            echo date('Y-m-d H:i:s').': Data is actual ('.$dateFrom.')'."\r\n";
            return false;
        }

        echo 'Period: '.$dateFrom.' - '.$dateTo."\r\n";
        echo 'Campaigns: '.($campaignIds ? implode(',', $campaignIds) : 'all')."\r\n";

        $campaignIds = array_merge($campaignIds, (clone $campaignsQuery)->pluck('id')->toArray());

        // Собираем из базы все ключевики, входящие в РК
        $keywordsQuery = Keyword::query()
            ->join('campaign_keywords', 'campaign_keywords.keyword_id', '=', 'keywords.id')
            ->join('campaign_products', 'campaign_products.id', '=', 'campaign_keywords.campaign_product_id')
            ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
            ->join('categories', 'categories.id', '=', 'keywords.category_id')
            ->when($campaignIds, function (Builder $query) use ($campaignIds) {
                $query->whereIn('campaigns.id', $campaignIds);
            })
            ->whereNotIn('campaign_products.status_id', [Status::DELETED])
            ->whereNotIn('campaign_keywords.status_id', [Status::DELETED])
            ->orderBy('campaigns.id', 'desc')
            ->orderBy('campaign_products.id', 'desc')
                                ->select('keywords.id', 'keywords.name', 'categories.ozon_id as category_id');

        $vaService = VirtualAssistantService::connect();

        // разбивка по кучкам, чтобы сервис не падал
        $keywordsQuery->chunk(1000, function (Collection $keywords) use ($vaService, $dateFrom, $dateTo)
        {
            echo $keywords->count().' шт. ключевых слов'."\r\n";
            // Запрос в ВП для получения популярностей
            $popularities = $vaService->getKeywordsPopularity($keywords, $dateFrom, $dateTo);
            if( $popularities === false ) {
                var_dump(VirtualAssistantService::getLastError());
                return false;
            }

            // Сохраняем ключевики в базу
            DatabaseService::savePopularities($popularities);
            echo date('Y-m-d H:i:s').': Popularity saved to keywords'."\r\n";

            return true;
        });

        /* Обработка статистики */

        // Разбивка по кампаниям, чтобы сервис не падал
        $campaigns = $campaignsQuery->get();
        foreach( $campaigns as $campaign )
        {
            echo 'Campaign: '.$campaign->id."\r\n";

            $lastSavedDate = DatabaseService::updatePopularitiesInStatistics($campaign, $dateFrom, $dateTo);

            if( $lastSavedDate ) {
                $campaign->vp_data_date = $lastSavedDate;
                $campaign->last_vp_sync = Carbon::now();
                $campaign->save();
            }
        }

        echo date('Y-m-d H:i:s').': Popularity load successfully'."\r\n";

        return true;
    }
}
