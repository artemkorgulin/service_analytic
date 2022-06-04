<?php

namespace App\Http\Controllers\Api\V2\Campaign;

use App\Contracts\Repositories\CampaignRepositoryInterface;
use App\Http\Requests\V2\Campaign\CampaignsFilterRequest;
use App\Http\Requests\V2\Campaign\CampaignStatisticRequest;
use App\Jobs\Ozon\UpdateCampaignKeywords;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\JsonResponse;
use App\Models\Campaign;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Campaign\CampaignSaveRequest;

class CampaignController extends Controller
{
    /**
     * Журнал кампаний и фильтрация
     *
     * @param  CampaignsFilterRequest  $request
     * @return JsonResponse
     */
    public function index(CampaignsFilterRequest $request, CampaignRepositoryInterface $campaignRepository): JsonResponse
    {
        if (!empty($request->input('search'))) {
            $campaignsData = $campaignRepository->allByFilters($request)->get();

            return response()->api(true, $campaignsData, []);
        }

        $campaignsData = $campaignRepository->allByFilters($request);
        $campaignFilterOptions = $campaignRepository->getCampaignFilterOptions();
        $campaignsTotalStatistic = $campaignRepository->getTotalStatistic($campaignsData->get()->pluck('id')->toArray());

        return response()->api_success([
            'campaigns' => PaginatorHelper::addPagination($request, $campaignsData),
            'filters' => $campaignFilterOptions,
            'total_statistic' => $campaignsTotalStatistic
        ]);
    }

    /**
     * Статистика по кампаниям по дням
     *
     * @param  CampaignStatisticRequest  $request
     * @return mixed
     */
    public function campaignsStatistic(CampaignStatisticRequest $request, CampaignRepositoryInterface $campaignRepository)
    {
        $campaignsStatistic = $campaignRepository->getStatistic($request);
        $campaignsTotalStatistic = $campaignRepository->getTotalStatistic($request->campaign_ids);

        return response()->api_success(['statistics' => $campaignsStatistic, 'total_statistic' => $campaignsTotalStatistic]);
    }

    /**
     * Поиск рекламных кампаний по названию
     *
     * @param  CampaignsFilterRequest  $request
     * @return JsonResponse
     */
    public function search(CampaignsFilterRequest $request, CampaignRepositoryInterface $campaignRepository): JsonResponse
    {
        $campaignsData = $campaignRepository->allByFilters($request)->get();

        return response()->api_success($campaignsData);
    }

    /**
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function show(Campaign $campaign, CampaignRepositoryInterface $campaignRepository): JsonResponse
    {
        if ($campaign->user_id !== UserService::getUserId() || in_array($campaign->account_id, UserService::getAllAccounts())) {
            return response()->api_fail('Нет доступа к указанной кампании', [], 403);
        }

        $campaign->load([
            'paymentType',
            'strategy',
            'campaignStatus',
            'placement',
            'campaignType',
            'sumStatistics',
            'strategyShowCounts',
            'strategyCpoCounts'
        ]);
        $campaignFilterOptions = $campaignRepository->getCampaignFilterOptions();
        $result = array_merge($campaign->toArray(), ['filters' => $campaignFilterOptions]);

        return response()->api_success($result);
    }

    /**
     * @return mixed
     */
    public function getAllFilters(CampaignRepositoryInterface $campaignRepository)
    {
        return response()->api_success($campaignRepository->getCampaignFilterOptions());
    }

    /**
     * @param  CampaignSaveRequest  $request
     * @return JsonResponse
     */
    public function store(CampaignSaveRequest $request): JsonResponse
    {
        $campaign = new Campaign();
        $campaign = $campaign->saveCampaign($request->input());
        UpdateCampaignKeywords::dispatch($campaign, UserService::getCurrentAccount());

        return response()->api_success($campaign->toArray());
    }

    /**
     * @param  CampaignSaveRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function update(CampaignSaveRequest $request, Campaign $campaign): JsonResponse
    {
        $campaign = $campaign->saveCampaign($request->input());
        UpdateCampaignKeywords::dispatch($campaign, UserService::getCurrentAccount());

        return response()->api_success($campaign->toArray());
    }

    /*public function storeOzon(CampaignStoreOzonRequest $request, $id)
    {
        $campaign = $this->campaignRepository->getItem($id);
        $campaign->fill($request->input());
        $campaign->budget = $campaign->budget * OzonHelper::BUDGET_COEFFICIENT;
        $campaignResult = $campaign->update();

        $ozonResult = (new OzonHelper())->ozonUpdateCampaign($campaign->id);

        if ($campaign->campaign_status_id === Status::ACTIVE) {
            (new OzonHelper())->ozonActivateCampaign($campaign->ozon_id);
        }else{
            (new OzonHelper())->ozonDeactivateCampaign($campaign->ozon_id);
        }

        if ($campaignResult && !$campaign->ozon_id) {
            $ozonResult = (new OzonHelper())->ozonCreateCampaign($campaign);
            if ($ozonResult) {
                $campaign->ozon_id = $ozonResult->campaignId;
                $this->updateCampaignStatus($campaign);
            }
        }

        return response()->json(
            [
                'success' => $ozonResult ?? false,
                'data'    => [
                    'result' => $ozonResult ?? false,
                ],
                'errors'  => [OzonPerfomanceService::getLastError()],
            ]
        );
    }*/

    /*protected function updateCampaignStatus(Campaign $campaign)
    {
        $curDate = date("Y-m-d");
        if ($campaign->start_date && $campaign->start_date < $curDate) {
            $status = $this->campaignStatusRepository
                ->getByCode(CampaignStatus::INACTIVE);
        } else {
            (new OzonHelper())->ozonActivateCampaign($campaign->ozon_id);
            if ($campaign->start_date == $curDate || !$campaign->start_date) {
                $status = $this->campaignStatusRepository
                    ->getByCode(CampaignStatus::ACTIVE);
            } else {
                $status = $this->campaignStatusRepository
                    ->getByCode(CampaignStatus::PLANNED);
            }
        }
        $campaign->campaign_status_id = $status->id;
        $campaign->save();
    }*/
}
