<?php

namespace Tests\Api\V2\Campaign;

use App\Models\Campaign;
use App\Models\CampaignPaymentType;
use App\Models\CampaignPlacement;
use App\Models\Strategy;
use App\Models\StrategyCpo;
use App\Models\StrategyHistory;
use App\Models\StrategyShows;
use App\Models\StrategyType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use DatabaseTransactions;

    private array $campaignDataStructure = [
        'id',
        'name',
        'budget',
        'placement_id',
        'payment_type_id',
        'campaign_status_id',
        'user_id',
        'account_id',
        'type_id',
        'page_type_id',
        'updated_at',
        'created_at',
        'payment_type',
        'strategy',
        'strategy_show_counts',
        'strategy_cpo_counts',
        'campaign_status',
        'placement',
        'campaign_type',
        'sum_statistics' => [
            'popularity',
            'shows',
            'clicks',
            'cost',
            'orders',
            'profit',
            'drr',
            'cpo',
            'acos',
            'purchased_shows',
            'ctr',
            'avg_1000_shows_price',
            'avg_click_price'
        ]
    ];

    /**
     * Test campaigns list method.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns', config('app.url')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'campaign_status_ids' => [2, 6]
                ]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'campaigns' => [
                    'current_page',
                    'data' => [
                        '*' => $this->campaignDataStructure
                    ],
                    'total'
                ],
                'filters',
                'total_statistic'
            ],
            'errors',
            'success'
        ]);
        $count = count($response->json('data.campaigns.data'));
        $this->assertEquals($count, 11);
    }

    /**
     * Test get null campaigns list.
     *
     * @return void
     */
    public function testIndexNull()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns', config('app.url')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'campaign_status_ids' => [4]
                ]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['campaigns', 'filters', 'total_statistic'],
            'errors',
            'success'
        ]);
        $count = count($response->json('data.campaigns.data'));
        $this->assertEquals($count, 0);
    }

    /**
     * Test get campaign filters method.
     *
     * @return void
     */
    public function testCampaignFilters()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaign-filters', config('app.url')),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'campaign_statuses',
                'strategy_types',
                'campaign_types',
                'campaign_placements'
            ],
            'errors',
            'success'
        ]);
    }

    /**
     * Test get campaign search method.
     *
     * @return void
     */
    public function testCampaignsSearch()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns-search', config('app.url')),
            array_merge($this->getRequestUserData(), ['search' => 'в карточках']),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['*' => $this->campaignDataStructure],
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $this->assertEquals(count($response['data']), 2);
    }

    public function testCampaignsStatistic()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns-statistic', config('app.url')),
            array_merge($this->getRequestUserData(), ['campaign_ids' => $this->getDataKey('campaigns.ids')]),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'statistics' => [
                    '*' => [
                        'date',
                        'popularity',
                        'shows',
                        'clicks',
                        'cost',
                        'orders',
                        'profit',
                        'drr',
                        'cpo',
                        'acos',
                        'purchased_shows',
                        'ctr',
                        'avg_1000_shows_price',
                        'avg_click_price'
                    ]
                ],
                'total_statistic' => [
                    'popularity',
                    'shows',
                    'clicks',
                    'cost',
                    'orders',
                    'profit',
                    'drr',
                    'cpo',
                    'acos',
                    'purchased_shows',
                    'ctr',
                    'avg_1000_shows_price',
                    'avg_click_price'
                ]
            ],
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $this->assertEquals(count($response['data']['statistics']), 17);

        // Тестирование фильтров по датам
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns-statistic', config('app.url')),
            array_merge($this->getRequestUserData(), ['campaign_ids' => $this->getDataKey('campaigns.ids'), 'start_date' => '2021-02-01']),
            $this->getRequestHeader()
        );
        $response = json_decode($response->content(), true);
        $this->assertEquals(count($response['data']['statistics']), 10);

        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns-statistic', config('app.url')),
            array_merge($this->getRequestUserData(), [
                'campaign_ids' => $this->getDataKey('campaigns.ids'),
                'start_date' => '2021-02-01',
                'end_date' => '2021-02-05'
            ]),
            $this->getRequestHeader()
        );
        $response = json_decode($response->content(), true);
        $this->assertEquals(count($response['data']['statistics']), 5);
    }

    /**
     * Test show campaign method.
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns/%s', config('app.url'), $this->getDataKey('campaigns.ids.2')),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => $this->campaignDataStructure,
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $campaignId = $response['data']['id'];
        $this->assertEquals($this->getDataKey('campaigns.ids.2'), $campaignId);
        $this->assertNotEmpty($response['data']['sum_statistics']['cost']);
    }

    /**
     * Test show campaign not access.
     *
     * @return void
     */
    public function testNotAccessShow()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaigns/%s', config('app.url'), $this->getDataKey('campaigns.not_access_ids.0')),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );
        $response->assertStatus(403);
        $response = json_decode($response->content(), true);
        $this->assertEquals('Нет доступа к указанной кампании', $response['error']['message']);
    }

    public function testStore()
    {
        $placementId = CampaignPlacement::first()->id;
        $paymentId = CampaignPaymentType::first()->id;
        $strategyTypeId = StrategyType::OPTIMAL_SHOWS;
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaigns', config('app.url')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'name' => 'Test campaign',
                    'budget' => 500,
                    'placement_id' => $placementId,
                    'payment_type_id' => $paymentId,
                    'strategy_type_id' => $strategyTypeId,
                    'start_date' => Carbon::now()->subDays(2)->toDateString(),
                    'end_date' => Carbon::now()->subDay()->toDateString(),
                    'step' => 5,
                    'threshold' => 0.9
                ]
            ),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => $this->campaignDataStructure,
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $campaignId = $response['data']['id'];
        $countInTable = Campaign::where('id', '=', $campaignId)->count();
        $this->assertEquals(1, $countInTable);
        $strategy = Strategy::where('campaign_id', '=', $campaignId);
        $this->assertEquals(1, $strategy->count());
        $strategy = $strategy->first();
        $strategyCounters = StrategyShows::where('strategy_id', '=', $strategy->id);
        $this->assertEquals(1, $strategyCounters->count());
        $strategyCounters = $strategyCounters->first();
        $this->assertEquals(5, $strategyCounters->step);
        $strategyCpoCounters = StrategyCpo::where('strategy_id', '=', $strategy->id)->count();
        $this->assertEquals(0, $strategyCpoCounters);
        $strategyHistory = StrategyHistory::where('strategy_id', '=', $strategy->id)->count();
        $this->assertEquals(0, $strategyHistory);
    }

    public function testUpdate()
    {
        $placementId = CampaignPlacement::first()->id;
        $paymentId = CampaignPaymentType::first()->id;
        $strategyTypeId = StrategyType::OPTIMAL_SHOWS;
        $campaignRequestData = [
            'name' => 'Test campaign',
            'budget' => 500,
            'placement_id' => $placementId,
            'payment_type_id' => $paymentId,
            'strategy_type_id' => $strategyTypeId,
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->subDay()->toDateString(),
            'step' => 5,
            'threshold' => 0.9
        ];
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaigns', config('app.url')),
            array_merge($this->getRequestUserData(), $campaignRequestData),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => $this->campaignDataStructure, 'errors', 'success']);
        $response = json_decode($response->content(), true);
        $campaignId = $response['data']['id'];
        $strategy = Strategy::where('campaign_id', '=', $campaignId);
        $this->assertEquals(1, $strategy->count());
        $strategy = $strategy->first();
        $strategyCounters = StrategyShows::where('strategy_id', '=', $strategy->id)->first();
        $this->assertEquals($campaignRequestData['step'], $strategyCounters->step);
        $strategyHistory = StrategyHistory::where('strategy_id', '=', $strategy->id)->count();
        $this->assertEquals(0, $strategyHistory);

        $campaignRequestData['step'] = 10;
        $campaignRequestData['name'] = 'Update camp';
        $response = $this->json(
            'PUT',
            sprintf('%s/api/v2/campaigns/%d', config('app.url'), $campaignId),
            array_merge($this->getRequestUserData(), $campaignRequestData),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => $this->campaignDataStructure, 'errors', 'success']);
        $response = json_decode($response->content(), true);
        $this->assertEquals($campaignRequestData['name'], $response['data']['name']);
        $strategyCounters = StrategyShows::where('strategy_id', '=', $strategy->id)->first();
        $this->assertEquals($campaignRequestData['step'], $strategyCounters->step);
        $strategyHistory = StrategyHistory::where('strategy_id', '=', $strategy->id);
        $this->assertEquals(1, $strategyHistory->count());
    }

    public function testStoreValidation()
    {
        $campaignRequestData = [
            'name' => 'Test campaign',
            'budget' => ''
        ];
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaigns', config('app.url')),
            array_merge($this->getRequestUserData(), $campaignRequestData),
            $this->getRequestHeader()
        );

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['placement_id', 'payment_type_id', 'strategy_type_id']]);
    }

    public function testUpdateValidation()
    {
        $placementId = CampaignPlacement::first()->id;
        $paymentId = CampaignPaymentType::first()->id;
        $strategyTypeId = StrategyType::OPTIMAL_SHOWS;
        $campaignRequestData = [
            'name' => 'Test campaign',
            'budget' => '',
            'campaign_status_id' => 5,
            'placement_id' => $placementId,
            'payment_type_id' => $paymentId,
            'strategy_type_id' => 2,
            'tcpo' => '',
            'threshold1' => -2,
            'threshold2' => '',
            'threshold3' => '',
            'vr' => ''
        ];
        $response = $this->json(
            'PUT',
            sprintf('%s/api/v2/campaigns/%d', config('app.url'), $this->getDataKey('campaigns.ids.1')),
            array_merge($this->getRequestUserData(), $campaignRequestData),
            $this->getRequestHeader()
        );

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['placement_id', 'payment_type_id', 'strategy_type_id']]);
    }
}
