<?php

namespace App\Services;

use App\Helpers\OzonHelper;
use App\Models\CampaignKeyword;
use App\Models\CampaignKeywordStatistic;
use App\Models\Status;
use App\Models\StrategyCpo;
use App\Models\StrategyCpoKeywordStatistic;
use App\Models\StrategyCpoStatistic;
use App\Models\StrategyHistory;
use App\Models\StrategyShows;
use App\Models\StrategyShowsKeywordStatistic;
use App\Models\StrategyStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class StrategyService
{
    const WEEK_DAYS = 7;
    const LAG_DAYS = 3;

    /**
     * Применить стратегии Оптимального количества показов
     *
     * @param  int[]  $campaignIds
     */
    public static function applyOptimalShows($campaignIds = null)
    {
        // Ищем стратегии к кампаниям
        $strategies = StrategyShows::query()
            ->whereHas('strategy', function (Builder $query) use ($campaignIds) {
                $query
                    ->when($campaignIds, function (Builder $query) use ($campaignIds) {
                        $query->whereIn('campaign_id', $campaignIds);
                    })
                    ->where('strategy_status_id', StrategyStatus::ACTIVE)
                    ->where(function (Builder $query) {
                        $query->whereDate('last_applying_date', '<', Carbon::today())
                            ->orWhereNull('last_applying_date');
                    });
            })
            ->select('id', 'strategy_id', 'threshold', 'step')
            ->with('strategy.campaign', 'strategy.campaign.account')
            ->get();

        // 7 дней + временной лаг в 3 дня
        $last7days = Carbon::today()->subDays(self::WEEK_DAYS + self::LAG_DAYS);

        // Для каждой РК в стратегии
        foreach ($strategies as $strategyShows) {
            // Проверяем, все ли данные есть
            $campaign = $strategyShows->strategy->campaign;
            if (
                $campaign->ozon_data_date < Carbon::yesterday()->toDateString() ||
                $campaign->vp_data_date < Carbon::today()->subDays(self::LAG_DAYS)->toDateString()
            ) {
                // По такой РК слишком мало данных, пропускаем
                echo 'Мало данных: '.$campaign->id.' '.$campaign->ozon_data_date.' '.$campaign->vp_data_date."\r\n";
                continue;
            }

            // Собираем статистику за последние 7 дней
            $ksQuery = CampaignKeywordStatistic::query()
                ->whereHas('campaignKeyword', function (Builder $query) use ($strategyShows) {
                    $query->whereHas('campaignProduct', function (Builder $query) use ($strategyShows) {
                        $query->whereHas('campaign', function (Builder $query) use ($strategyShows) {
                            $query->where('id', $strategyShows->strategy->campaign_id);
                        });
                    });
                })
                ->whereDate('date', '>=', $last7days)
                ->select('campaign_keyword_id', 'shows', 'popularity', 'date')
                ->latest();

            $keywordStatistics = CampaignKeywordStatistic::query()
                ->fromSub($ksQuery, 'statistics')
                ->groupBy('campaign_keyword_id')
                ->select(
                    'campaign_keyword_id',
                    'date',
                    'shows as last_shows',
                    'popularity as last_popularity'
                )
                ->selectRaw('AVG(shows) as avg_shows')
                ->selectRaw('AVG(popularity) as avg_popularity')
                ->orderBy('campaign_keyword_id', 'desc')
                ->with('campaignKeyword')
                ->get();

            // Для каждого ключевика
            foreach ($keywordStatistics as $keywordStatistic) {
                // Если ключевик активен
                if ($keywordStatistic->campaignKeyword->status_id == Status::ACTIVE) {
                    // Пороговая популярность
                    $thresholdPopularity = $keywordStatistic->avg_popularity * $strategyShows->threshold;

                    // Если есть данные о популярности и показах
                    if ($keywordStatistic->avg_popularity > 0 && $keywordStatistic->avg_shows) {
                        // если среднее значение показов по всем ключевикам за последние 7 дней
                        // меньше или равно, чем среднее значение популярности по всем ключевикам за последние 7 дней
                        // умноженное на пороговое значение
                        if ($thresholdPopularity >= $keywordStatistic->avg_shows) {
                            // то ставка повышается на шаг стратегии
                            $keywordStatistic->campaignKeyword->bid += $strategyShows->step;
                        }
                        // если среднее значение показов по всем ключевикам за последние 7 дней
                        // больше, чем среднее значение популярности по всем ключевикам за последние 7 дней
                        // умноженное на пороговое значение
                        else {
                            // то ставка понижается на шаг стратегии
                            $keywordStatistic->campaignKeyword->bid -= $strategyShows->step;
                        }

                        $keywordStatistic->campaignKeyword->save();

                        // Отправляем в Озон
                        if (config('ozon.strategy_sync')) {
                            $ozonHelper = new OzonHelper($strategyShows->strategy->campaign->account);
                            $result = $ozonHelper->makeOzonBidUpdateRequest($keywordStatistic->campaignKeyword->id,
                                $keywordStatistic->campaignKeyword->bid);
                            if (!$result) {
                                echo __('strategy.error_ozon_bid_set', [
                                    'keyword' => $keywordStatistic->campaignKeyword->name,
                                    'keywordId' => $keywordStatistic->campaignKeyword->id,
                                    'strategy' => $strategyShows->strategy->strategyType->name,
                                ]);
                            }
                        }
                    }

                    // Сохраняем в историю
                    $strategyKeywordStatistic = new StrategyShowsKeywordStatistic();
                    $strategyKeywordStatistic->strategy_shows_id = $strategyShows->id;
                    $strategyKeywordStatistic->campaign_keyword_id = $keywordStatistic->campaignKeyword->id;
                    $strategyKeywordStatistic->date = Carbon::now();
                    $strategyKeywordStatistic->bid = $keywordStatistic->campaignKeyword->bid;
                    $strategyKeywordStatistic->popularity = $keywordStatistic->last_popularity ?? 0;
                    $strategyKeywordStatistic->threshold = $strategyShows->threshold;
                    $strategyKeywordStatistic->step = $strategyShows->step;
                    $strategyKeywordStatistic->avg_shows = $keywordStatistic->avg_shows;
                    $strategyKeywordStatistic->avg_popularity = $keywordStatistic->avg_popularity;
                    $strategyKeywordStatistic->threshold_popularity = $thresholdPopularity;
                    $strategyKeywordStatistic->save();
                }
            }

            $strategyShows->strategy->last_applying_date = Carbon::today();
            $strategyShows->strategy->save();
        }

        return true;
    }

    /**
     * Применить стратегии Оптимального CPO
     */
    public static function applyOptimalCpo()
    {
        // производим выборку компаний к которым применена стратегия CPO
        $strategies = StrategyCpo::query()
            ->whereHas('strategy', function (Builder $query) {
                $query->where('strategy_status_id', StrategyStatus::ACTIVE)
                    ->where(function (Builder $query) {
                        $query->whereDate('last_applying_date', '<', Carbon::now())
                            ->orWhereNull('last_applying_date');
                    });
            })
            ->select('id', 'strategy_id', 'tcpo', 'vr', 'horizon', 'threshold1', 'threshold2', 'threshold3')
            ->with('strategy.campaign')
            ->get();

        foreach ($strategies as $cpo) {
            $campaignId = $cpo->strategy->campaign_id;
            $horizon = Carbon::now()->subDays($cpo->horizon + 1);

            // подсчитываем кол-во заказов по каждой РК
            $orders = $cpo->strategy->campaign->statistics()
                ->where([
                    ['created_at', '>=', $horizon],
                    ['created_at', '<', Carbon::now()]
                ])
                ->sum('orders');

            // проверяем результат на минимальное код-во заказов
            // если кол-во меньше допустимого применение стратегии останавливается и стратегия переводится в статус "НЕ АКТИВНО"
            if ($orders < StrategyCpo::MIN_ORDERS) {
                StrategyHistory::create(
                    [
                        'strategy_id' => $cpo->strategy->id,
                        'parameter' => 'status_id',
                        'value_before' => $cpo->strategy->strategy_status_id,
                        'value_after' => StrategyStatus::NOT_ACTIVE
                    ]
                );
                $cpo->strategy->strategy_status_id = StrategyStatus::NOT_ACTIVE;
                $cpo->strategy->save();
            } else {
                // производим выборку по статистике ключевых слов для выбранной РК
                // в выборке участвую активные ключевые слова
                // группируем выборку по идентификатору ключевого слова в РК
                // производим расчеты статистических показателей
                $keywords = CampaignKeywordStatistic::query()
                    ->where([
                        ['date', '>=', $horizon],
                        ['date', '<', Carbon::now()]
                    ])
                    ->join('campaign_keywords', function ($join) {
                        $join->on('campaign_keyword_statistics.campaign_keyword_id', 'campaign_keywords.id')
                            ->where('campaign_keywords.status_id', Status::ACTIVE);
                    })
                    ->join('campaign_products', 'campaign_keywords.campaign_product_id', 'campaign_products.id')
                    ->join('campaigns', function ($join) use ($campaignId) {
                        $join->on('campaign_products.campaign_id', 'campaigns.id')
                            ->where('campaigns.id', $campaignId);
                    })
                    ->groupBy('campaign_keyword_statistics.campaign_keyword_id')
                    ->select('campaign_keyword_statistics.campaign_keyword_id', 'campaigns.campaign_status_id',
                        'campaign_keywords.group_id')
                    ->selectRaw('SUM(campaign_keyword_statistics.shows) as sum_shows')
                    ->selectRaw('SUM(campaign_keyword_statistics.orders) as sum_orders')
                    ->selectRaw('SUM(campaign_keyword_statistics.cost) as sum_cost')
                    ->selectRaw('SUM(campaign_keyword_statistics.clicks) as sum_clicks')
                    ->selectRaw('(SUM(campaign_keyword_statistics.orders) / (SUM(campaign_keyword_statistics.shows) / 1000)) as kvcr')
                    ->selectRaw('(SUM(campaign_keyword_statistics.cost) / SUM(campaign_keyword_statistics.orders)) as fcpo')
                    ->get();

                // высчитываем общие статистические показатели для рекламной компании
                $sumOrders = $keywords->sum('sum_orders');
                $sumCost = $keywords->sum('sum_cost');
                $sumShows = $keywords->where('sum_orders', '>', 0)->sum('sum_shows');
                $fcpo = $sumCost / $sumOrders;
                $ackvcr = $sumShows ? $sumOrders / ($sumShows / 1000) : 0;

                // вычисление ставку ключевого слова
                foreach ($keywords as $keyword) {
                    $vr = $cpo->vr / 100;
                    if ($keyword->sum_orders > 0) {
                        if ((1.05 * $cpo->tcpo) > $keyword->fcpo && $keyword->fcpo > (0.95 * $cpo->tcpo)) {
                            $sbid = $cpo->tcpo * $keyword->kvcr;
                        } else {
                            if ($keyword->fcpo > (1.05 * $cpo->tcpo)) {
                                $sbid = ($cpo->tcpo * $keyword->kvcr) * (1 - $vr);
                            } else {
                                if ($keyword->fcpo < (0.95 * $cpo->tcpo)) {
                                    $sbid = ($cpo->tcpo * $keyword->kvcr) * (1 + $vr);
                                }
                            }
                        }
                    } else {
                        if ($keyword->sum_clicks < $cpo->threshold1) {
                            $sbid = ($cpo->tcpo * $ackvcr) * (1 + $vr);
                        } else {
                            if ($keyword->sum_clicks > $cpo->threshold1 && $keyword->sum_clicks < $cpo->threshold2) {
                                $sbid = $cpo->tcpo * $ackvcr;
                            } else {
                                if ($keyword->sum_clicks > $cpo->threshold2 && $keyword->sum_clicks < $cpo->threshold3) {
                                    $sbid = ($cpo->tcpo * $ackvcr) * (1 - $vr);
                                } else {
                                    if ($keyword->sum_clicks > $cpo->threshold3) {
                                        $campaignKeyword = CampaignKeyword::find($keyword->campaign_keyword_id);
                                        $campaignKeyword->status_id = Status::NOT_ACTIVE;
                                    }
                                }
                            }
                        }
                    }
                    // округляем результирующее значение что бы получить правильное значение integer
                    $sbid = round($sbid);

                    // проверяем на минимально допустимое значение ставки
                    // проверяем активна ли текущая РК
                    if ($sbid > 35 && $keyword->campaign_status_id === Status::ACTIVE) {
                        // обновляем ставку ключевого слова
                        if (!isset($campaignKeyword)) {
                            $campaignKeyword = CampaignKeyword::find($keyword->campaign_keyword_id);
                        }
                        $campaignKeyword->bid = $sbid;

                        // Отправляем в Озон
                        if (config('ozon.strategy_sync')) {
                            $ozonHelper = new OzonHelper($cpo->strategy->campaign->account);
                            if (is_null($keyword->group_id)) {
                                $result = $ozonHelper->makeOzonBidUpdateRequest($keyword->campaign_keyword_id, $sbid);
                            } else {
                                $result = $ozonHelper->makeOzonGroupBidUpdateRequest($keyword->campaign_keyword_id,
                                    $sbid);
                            }

                            if (!$result) {
                                echo __(
                                    'strategy.error_ozon_bid_set',
                                    [
                                        'keywordId' => $keyword->campaign_keyword_id,
                                        'strategy' => $cpo->strategy->strategy_type_id,
                                    ]
                                );
                            }
                        }
                    }

                    // модель ключевого слова может изменится в 2 случаях
                    // при смене статуса по результатам расчетов
                    // при обновлении ставки
                    // выносим сохранение модели в конец итерации не повторять это действие дважды в одном цикле
                    if (isset($campaignKeyword)) {
                        $campaignKeyword->save();
                    }

                    // сохраняем результаты по каждой ставке в таблицу истории ставок стратегии CPO
                    StrategyCpoKeywordStatistic::create([
                        'strategy_cpo_id' => $cpo->id,
                        'campaign_keyword_id' => $keyword->campaign_keyword_id,
                        'conversion' => $keyword->kvcr ?? 0,
                        'fcpo' => $keyword->fcpo ?? 0,
                        'date' => Carbon::now(),
                        'bid' => $sbid
                    ]);
                }

                // сохраняем общие результаты РК в таблицу истории РК стратегии CPO
                StrategyCpoStatistic::create([
                    'strategy_cpo_id' => $cpo->id,
                    'conversion' => $ackvcr ?? 0,
                    'fcpo' => $fcpo ?? 0,
                    'orders' => $sumOrders ?? 0,
                    'date' => Carbon::now()
                ]);
            }

            // c каждым днем увеличиваем значение горизонта оценки на один день, до максимального значения
            if ($cpo->horizon < StrategyCpo::DAYS_IN_HORIZON) {
                $newHorizon = $cpo->horizon + 1;
                StrategyHistory::create(
                    [
                        'strategy_id' => $cpo->strategy->id,
                        'parameter' => 'horizon',
                        'value_before' => $cpo->horizon,
                        'value_after' => $newHorizon
                    ]
                );

                $cpo->horizon = $newHorizon;
                $cpo->save();
            }

            $cpo->strategy->last_applying_date = Carbon::now();
            $cpo->strategy->save();
        }
    }
}
