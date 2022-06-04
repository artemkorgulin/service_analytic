<?php

namespace App\Http\Controllers\Api;

use App\Helpers\OzonHelper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\CampaignKeyword;
use App\Models\Status;
use App\Models\Strategy;
use App\Models\StrategyCpo;
use App\Models\StrategyHistory;
use App\Models\StrategyShows;
use App\Models\StrategyStatus;
use App\Models\StrategyType;
use App\Services\OzonPerformanceService;
use App\Services\StrategyService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateController extends Controller
{
    protected array $errors = [];

    /**
     * Update bid for keywords
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updateKeywordBid(Request $request)
    {
        $request->validate([
            'keywordIds' => 'required|array',
            'bid' => 'integer|min:35|max:2000',
        ]);

        $campaignKeywordIds = $request->keywordIds;
        $newBid = $request->bid;

        $ozonHelper = new OzonHelper();

        $result = $this->updateKeyword($ozonHelper, $campaignKeywordIds, null, $newBid);

        return response()->json(
            [
                'success' => (bool) $result,
                'data' => $ozonHelper->getResponses(),
                'errors' => array_merge($this->errors, $ozonHelper->getErrors()),
            ]
        );
    }

    /**
     * Обновление ключевика
     *
     * @param  OzonHelper  $ozonHelper
     * @param  integer[]  $campaignKeywordIds
     * @param  integer  $newBid
     * @param  integer  $newStatus
     * @param  integer|bool  $groupId
     *
     * @return bool
     */
    protected function updateKeyword($ozonHelper, $campaignKeywordIds, $groupId, $newBid = null, $newStatus = null)
    {
        $campaignKeywordsQuery = CampaignKeyword::query()
            ->whereIn('id', $campaignKeywordIds)
            ->when($groupId, function (Builder $query) use ($groupId) {
                $query->where('group_id', $groupId);
            });

        $groupIds = (clone $campaignKeywordsQuery)->groupBy('group_id')->pluck('group_id');

        if (count($groupIds) > 1) {
            $this->errors[] = (__('updater.this_keywords_in_different_groups'));
            return false;
        }

        $groupId = $groupIds->first();

        if (is_null($groupId)) {
            // Ключевик не в группе
            $result = $ozonHelper->makeOzonBidUpdateRequest($campaignKeywordIds, $newBid, $newStatus);
        } else {
            // Ключевик в группе
            $result = $ozonHelper->makeOzonGroupBidUpdateRequest($campaignKeywordIds, $groupId, $newBid, $newStatus);

            $keywordsIds = (clone $campaignKeywordsQuery)->pluck('keyword_id');
            $campaignKeywordsQuery = CampaignKeyword::query()->where('group_id', $groupId)->whereIn('keyword_id',
                $keywordsIds);
        }

        if ($result && $newStatus) {
            $body = ['status_id' => $newStatus];
            if ($newStatus == Status::ARCHIVED) {
                $body['bid'] = 0;
            }
            (clone $campaignKeywordsQuery)->update($body);

            if (isset($body['bid'])) {
                return $result;
            }
        }

        if ($result && $newBid) {
            (clone $campaignKeywordsQuery)->update(['bid' => $newBid]);
        }

        return $result;
    }

    /**
     * Update status for keywords
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updateKeywordStatus(Request $request)
    {
        $request->validate([
            'keywordIds' => 'required|array',
            'status' => 'integer',
        ]);

        $campaignKeywordIds = $request->keywordIds;
        $statusId = $request->status;
        $groupId = $request->group_id ?? null;

        $ozonHelper = new OzonHelper();

        $result = $this->updateKeyword($ozonHelper, $campaignKeywordIds, $groupId, null, $statusId);

        return response()->json(
            [
                'success' => (bool) $result,
                'data' => $ozonHelper->getResponses(),
                'errors' => array_merge($this->errors, $ozonHelper->getErrors()),
            ]
        );
    }

    /**
     * Update status for campaign
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updateCampaignStatus(Request $request)
    {
        $request->validate([
            'campaignId' => 'required|array',
            'status' => 'required|integer'
        ]);

        $statusId = $request->status;
        $status = ($statusId == Status::ACTIVE) ? 'activate' : 'deactivate';

        $account = UserService::getCurrentAccount();
        $ozonConnection = OzonPerformanceService::connectRepeat($account);

        $result = true;
        $responses = [];
        $errors = [];

        foreach ($request->campaignId as $campaignId) {
            $campaign = Campaign::find($campaignId);

            $res = $ozonConnection->updateCampaignStatus($campaign->ozon_id, $status);

            if ($res) {
                //update in DB
                $campaign->campaign_status_id = $statusId;
                $campaign->save();
                $responses[] = $res;

                if ($statusId == Status::ARCHIVED) {
                    $result = CampaignKeyword::query()
                        ->whereIn('campaign_product_id', $campaignId)
                        ->update([
                            'status_id' => $statusId,
                            'bid' => 0
                        ]);
                }
            } else {
                $result = false;
                $errors[] = $ozonConnection::getLastError();
            }
        }

        return response()->json(
            [
                'success' => $result,
                'data' => $responses,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Update status for campaign products
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateCampaignProductStatus(Request $request)
    {
        $request->validate([
            'campaignProductId' => 'required|array',
            'campaignProductId.*' => 'integer',
            'statusId' => 'required|integer'
        ]);

        $statusId = $request->statusId;

        $result = CampaignProduct::query()
            ->whereIn('id', $request->campaignProductId)
            ->update(['status_id' => $statusId]);

        if ($result && $statusId == Status::ARCHIVED) {
            $result = CampaignKeyword::query()
                ->whereIn('campaign_product_id', $request->campaignProductId)
                ->update([
                    'status_id' => $statusId,
                    'bid' => 0
                ]);
        }

        return response()->json(
            [
                'success' => (bool) $result,
                'data' => [],
                'errors' => [],
            ]
        );
    }

    /***************************/
    /* Управление ставками 2.0 */
    /***************************/

    /**
     * Добавление новой стратегии
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function addNewStrategy(Request $request)
    {
        $request->validate(
            [
                'strategyTypeId' => 'required|integer',
                'campaignId' => 'required|integer'
            ]
        );

        $strategyTypeId = $request->input('strategyTypeId');
        $campaignId = $request->input('campaignId');
        $action = $request->input('action');
        $notify = [];

        // Уникальные значения и проверки для разных типов стратегий
        $strategy = new Strategy();
        $campaign = Campaign::find($campaignId);
        $dateNow = Carbon::now();

        switch ($strategyTypeId) {
            case StrategyType::OPTIMAL_SHOWS:
                $check = $campaign->statistics()
                    ->where('created_at', '>=', $dateNow->subDays(StrategyShows::DAYS_IN_HORIZON))
                    ->count();

                if ($check < StrategyShows::DAYS_IN_HORIZON) {
                    return response()->json(
                        [
                            'success' => false,
                            'data' => [],
                            'errors' => [__('updater.strategy_not_enough_statistic')],
                        ]
                    );
                }

                $strategyData = new StrategyShows();

                $strategyData->threshold = 0.8;
                $strategyData->step = 5;

                $strategyQuery = $strategy->strategyShows();
                break;

            case StrategyType::OPTIMIZE_CPO:
                $duration = $campaign->created_at->diff($dateNow)->days;
                $strategyData = new StrategyCpo();
                $daysInHorizon = StrategyCpo::DAYS_IN_HORIZON;

                $strategyData->horizon = $duration >= $daysInHorizon ? $daysInHorizon : $duration - 1;

                $countOrder = $campaign->statistics()
                    ->where([
                        ['created_at', '>=', $dateNow->subDays($strategyData->horizon)],
                        ['created_at', '<', Carbon::now()],
                    ])
                    ->sum('orders');

                if ($countOrder < StrategyCpo::MIN_ORDERS) {
                    return response()->json(
                        [
                            'success' => false,
                            'data' => [],
                            'errors' => [__('updater.strategy_min_orders', ['campaign' => $campaign->name])],
                        ]
                    );
                }

                $strategyQuery = $strategy->strategyCpo();
                break;
        }

        // Обработка существующих стратегий, принудительная смена стратегии или отмена процедуры
        // Принудительная смена или отмена зависит от значения переменной <action> в запросе
        $strategyExist = Strategy::query()->where('campaign_id', $campaignId);

        if ($strategyExist->exists() && $action !== 'force') {
            $oldStrategy = $strategyExist->first();
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'errors' => [],
                    'force' => __('updater.this_campaign_already_with_strategy', [
                        'campaign' => $oldStrategy->campaign()->value('name'),
                        'strategy' => $oldStrategy->strategyType()->value('name')
                    ])
                ]
            );
        }

        // Создаем новую стратегию
        $strategy->strategy_type_id = $strategyTypeId;
        $strategy->campaign_id = $campaignId;
        // Значения по-умолчанию:
        $strategy->strategy_status_id = StrategyStatus::ACTIVE;
        $strategy->activated_at = Carbon::now();

        // Сохранение новой стратегии
        // Удаление прежней стратегии в случае принудительного режима применения стратегии
        $res = DB::transaction(function () use ($strategy, $strategyExist, $action) {
            $checkQuery = true;
            if ($strategyExist->exists() && $action === 'force') {
                $checkQuery = $strategyExist->delete();
            }
            if ($checkQuery) {
                $checkQuery = $strategy->save();
            }
            return $checkQuery;
        });

        if (!$res) {
            return response()->json(
                [
                    'success' => false,
                    'data' => $res,
                    'errors' => [__('updater.strategy_create_error')],
                ]
            );
        }

        if (isset($strategyQuery) && isset($strategyData)) {
            $res = $strategyQuery->save($strategyData);

            if ($res) {
                if ($strategyTypeId === StrategyType::OPTIMAL_SHOWS) {
                    $applyResult = StrategyService::applyOptimalShows([$campaign->id]);

                    $notify[] = $applyResult ? __('updater.strategy_apply_done') : __('updater.strategy_apply_bad');
                } else {
                    $notify[] = __('updater.strategy_create_success');
                }
            }


        } else {
            $res = false;
        }

        return response()->json(
            [
                'success' => (bool) $res,
                'errors' => [],
                'data' => [
                    'notify' => $notify
                ]
            ]
        );
    }

    /**
     * Обновление статуса стратегии
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateStrategyStatus(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required',
                'strategyId.*' => 'integer',
                'statusId' => 'required|integer',
            ]
        );

        $strategiesIds = (array) $request->get('strategyId');
        $statusId = $request->get('statusId');

        // Старые данные
        $strategies = Strategy::query()->whereIn('id', $strategiesIds)->select('id', 'strategy_status_id');
        $oldStatusesCollection = $strategies->get();

        // Обновление стратегии
        $update = [
            'strategy_status_id' => $statusId
        ];
        if ($statusId == Status::ACTIVE) {
            $update['activated_at'] = Carbon::now();
        }
        $res = $strategies->update($update);

        // Сохранение изменений в историю
        foreach ($strategiesIds as $strategyId) {
            $oldStatusId = $oldStatusesCollection->where('id', $strategyId)->pluck('strategy_status_id')->first();
            StrategyHistory::create(
                [
                    'strategy_id' => $strategyId,
                    'parameter' => 'status_id',
                    'value_before' => $oldStatusId,
                    'value_after' => $statusId
                ]
            );
        }

        return response()->json(
            [
                'success' => $res > 0,
                'data' => [
                    'updatedRows' => $res,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Обновление порогового значения стратегии
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateStrategyThreshold(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required',
                'strategyId.*' => 'integer',
                'threshold' => 'required|numeric',
            ]
        );

        $strategiesIds = (array) $request->get('strategyId');
        $threshold = $request->get('threshold');

        // Старые данные
        $strategiesShows = StrategyShows::query()
            ->whereIn('strategy_id', $strategiesIds)
            ->select('strategy_id', 'threshold');
        $oldThresholdCollection = $strategiesShows->get();

        // Обновление стратегии
        $res = $strategiesShows->update(['threshold' => $threshold]);

        // Сохранение изменений в историю
        foreach ($strategiesIds as $strategyId) {
            $oldThreshold = $oldThresholdCollection->where('strategy_id', $strategyId)
                ->pluck('threshold')
                ->first();
            StrategyHistory::create(
                [
                    'strategy_id' => $strategyId,
                    'parameter' => 'threshold',
                    'value_before' => $oldThreshold,
                    'value_after' => $threshold
                ]
            );
        }

        return response()->json(
            [
                'success' => $res > 0,
                'data' => [
                    'updatedRows' => $res,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Обновление шага стратегии
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateStrategyStep(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required',
                'strategyId.*' => 'integer',
                'step' => 'required|numeric',
            ]
        );

        $strategiesIds = (array) $request->get('strategyId');
        $step = $request->get('step');

        // Старые данные
        $strategiesShows = StrategyShows::query()
            ->whereIn('strategy_id', $strategiesIds)
            ->select('strategy_id', 'step');
        $oldStepCollection = $strategiesShows->get();

        // Обновление стратегии
        $res = $strategiesShows->update(['step' => $step]);

        // Сохранение изменений в историю
        foreach ($strategiesIds as $strategyId) {
            $oldStep = $oldStepCollection->where('strategy_id', $strategyId)->pluck('step')->first();
            StrategyHistory::create(
                [
                    'strategy_id' => $strategyId,
                    'parameter' => 'step',
                    'value_before' => $oldStep,
                    'value_after' => $step
                ]
            );
        }

        return response()->json(
            [
                'success' => $res > 0,
                'data' => [
                    'updatedRows' => $res,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Обновление значение целевого CPO
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateStrategyTcpo(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required',
                'strategyId.*' => 'integer',
                'tcpo' => 'required|numeric|min:'.StrategyCpo::MIN_TCPO,
            ]
        );

        $strategiesIds = (array) $request->get('strategyId');
        $tcpo = $request->get('tcpo');

        // Старые данные
        $strategiesCpo = StrategyCpo::query()
            ->whereIn('strategy_id', $strategiesIds)
            ->select('strategy_id', 'tcpo');
        $oldTcpoCollection = $strategiesCpo->get();

        // Обновление стратегии
        $res = $strategiesCpo->update(['tcpo' => $tcpo]);

        // Сохранение изменений в историю
        foreach ($strategiesIds as $strategyId) {
            $oldTcpo = $oldTcpoCollection->where('strategy_id', $strategyId)->pluck('tcpo')->first();
            StrategyHistory::create(
                [
                    'strategy_id' => $strategyId,
                    'parameter' => 'tcpo',
                    'value_before' => $oldTcpo,
                    'value_after' => $tcpo
                ]
            );
        }

        return response()->json(
            [
                'success' => $res > 0,
                'data' => [
                    'updatedRows' => $res,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Удаление стратегии
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function deleteStrategy(Request $request)
    {
        $request->validate(
            [
                'strategyId' => 'required|integer',
            ]
        );

        $strategyId = $request->get('strategyId');

        $res = Strategy::destroy($strategyId);

        return response()->json(
            [
                'success' => $res > 0,
                'errors' => [],
            ]
        );
    }

    /**
     * Обновление стратегии CPO
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateStrategyCpo(Request $request)
    {
        $strategyId = $request->get('strategyId');
        $campaignId = $request->get('campaignId');
        $changes = [];

        // формируем список полей для обновления сущности компании
        $fieldCampaign = [
            'budget' => $request->get('dailyBudget')
        ];

        // обновление сущности компании
        $campaign = Campaign::find($campaignId);
        foreach ($fieldCampaign as $field => $value) {
            if ($campaign->{$field} !== $value) {
                $campaign->{$field} = $value;

                $checkQuery = $campaign->save();

                if ($checkQuery && $field === 'budget') {
                    $ozonConnection = OzonPerformanceService::connectRepeat(UserService::getCurrentAccount());

                    $res = $ozonConnection->updateCampaign($campaignId, [
                        'dailyBudget' => $value
                    ]);

                    if (!$res) {
                        return response()->json(
                            [
                                'success' => false,
                                'errors' => $ozonConnection::getLastError()
                            ]
                        );
                    }
                }
            }
        }
        unset($field);

        // формируем список полей для обновления сущности стратегии CPO
        $fieldCpo = [
            'threshold1' => $request->get('threshold1'),
            'threshold2' => $request->get('threshold2'),
            'threshold3' => $request->get('threshold3'),
            'tcpo' => $request->get('tcpo'),
            'vr' => $request->get('vr')
        ];

        // обновление сущности стратегии CPO
        $strategy = Strategy::find($strategyId);
        foreach ($fieldCpo as $field => $value) {
            $column = $strategy->strategyCpo->{$field};

            if ($column !== $value) {
                $changes[] = [
                    'strategy_id' => $strategyId,
                    'parameter' => $field,
                    'value_before' => $column,
                    'value_after' => $value
                ];
                $strategy->strategyCpo->{$field} = $value;
            }
        }
        unset($field);

        if (count($changes)) {
            if ($strategy->strategyCpo->save()) {
                foreach ($changes as $change) {
                    StrategyHistory::create($change);
                }
            } else {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => [__('updater.strategy_update_error')],
                    ]
                );
            }
        }

        return response()->json(
            [
                'success' => true,
                'errors' => [],
            ]
        );
    }
}
