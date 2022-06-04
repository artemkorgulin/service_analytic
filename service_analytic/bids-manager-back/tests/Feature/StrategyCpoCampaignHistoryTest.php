<?php

namespace Tests\Feature;

use App\Helpers\StrategyHelper;
use App\Models\Status;
use App\Models\StrategyCpo;
use App\Traits\UserForTest;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class StrategyCpoCampaignHistoryTest extends TestCase
{
    use UserForTest;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $strategyCpo = StrategyCpo::whereHas('strategy', function (Builder $query) {
                $query->where('strategy_status_id', Status::ACTIVE)
                    ->whereNotNull('last_applying_date');
            })
            ->first();

        $dateRange = StrategyHelper::getDefaultHorizonStrategyCpo($strategyCpo->strategy_id);

        $response = $this->json('GET', config('app.url') . '/api/get-strategy-campaign-history', [
            'api_token'  => $this->getToken(),
            'from'       => $dateRange['from'],
            'to'         => $dateRange['to'],
            'strategyId' => $strategyCpo->strategy_id,
            'per_page'   => 10
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'strategyTypeId',
                'strategyId',
                'strategyName',
                'strategyBehavior',
                'campaignId',
                'campaignName',
                'campaignBudget',
                'strategyLastChangedDate',
                'strategyThreshold1',
                'strategyThreshold2',
                'strategyThreshold3',
                'strategyTcpo',
                'strategyVr',
                'dashboard' => [
                    'shows',
                    'kvcr',
                    'clicks',
                    'ctr',
                    'avg_click_price',
                    'avg_1000_shows_price',
                    'cost',
                    'orders',
                    'profit',
                    'fcpo',
                    'acos'
                ],
                'history' => [
                    'current_page',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                    'data' => [
                        '*' => [
                            'acos',
                            'avg_1000_shows_price',
                            'avg_click_price',
                            'clicks',
                            'cost',
                            'ctr',
                            'fcpo',
                            'group_id',
                            'group_name',
                            'id',
                            'keyword_name',
                            'keyword_status_id',
                            'kvcr',
                            'orders',
                            'ozon_id',
                            'profit',
                            'shows',
                            'sku',
                            'sku_url',
                            'status_name'
                        ]
                    ],
                ]
            ],
            'success',
            'errors'
        ]);
    }
}
