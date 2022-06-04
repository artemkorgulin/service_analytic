<?php

namespace Tests\Ozon;

use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignListDTO;
use App\Helpers\OzonHelper;
use App\Models\Campaign;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OzonPerformanceServiceTest extends TestCase
{

    use DatabaseTransactions;

    public OzonPerformanceService $ozonPerformanceService;


    public function testGetClientCampaigns()
    {
        $campaignList = $this->ozonPerformanceService->getClientCampaigns();
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertInstanceOf(CampaignListDTO::class, $campaignList);
        $columns = [
            'id',
            'title',
            'state',
            'advObjectType',
            'fromDate',
            'toDate',
            'dailyBudget',
            'placement',
            'budget',
            'createdAt',
            'updatedAt'
        ];

        $campaign = $campaignList->list[0];
        foreach ($columns as $key) {
            $this->assertObjectHasAttribute($key, $campaign);
        }
    }


    public function testGetClientCampaignObjects()
    {
        $response = $this->ozonPerformanceService->getClientCampaignObjects($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertObjectHasAttribute('id', $response->list[0]);
    }


    public function testGetCampaignProducts()
    {
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ids.0'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $columns = [
            'sku',
            'bid',
            'groupId',
            'stopWords',
            'phrases',
            'auctionStatus'
        ];

        foreach ($columns as $key) {
            $this->assertObjectHasAttribute($key, $response->products[0]);
        }
    }


    public function testGetClientStatistics()
    {
        $campaign = Campaign::find($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.0'));
        $params   = [
            'campaigns' => [$campaign->ozon_id],
            'dateFrom'  => $campaign->start_date,
            'dateTo'    => $campaign->end_date,
            'groupBy'   => 'DATE'
        ];
        $response = $this->ozonPerformanceService->requestClientStatistics($params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertArrayHasKey('UUID', $response);
    }


    public function testCheckClientStatisticsReport()
    {
        $uuid     = '38a4617e-7cf0-4719-95de-7948cb1776f9';
        $response = $this->ozonPerformanceService->checkClientStatisticsReport($uuid);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $columns = [
            'UUID',
            'state',
            'createdAt',
            'updatedAt',
            'request',
            'link'
        ];

        foreach ($columns as $key) {
            $this->assertObjectHasAttribute($key, $response);
        }
    }


    public function testGetClientStatisticsReport()
    {
        $uuid     = '38a4617e-7cf0-4719-95de-7948cb1776f9';
        $response = $this->ozonPerformanceService->getClientStatisticsReport($uuid);
        $file     = $response->getContents();
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertStringContainsString('Поисковый запрос;краска', $file);
    }


    public function testGetClientStatisticsPhrases()
    {
        $campaign        = Campaign::find($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.0'));
        $responseObjects = $this->ozonPerformanceService->getClientCampaignObjects($campaign->id);
        $params          = [
            'campaigns' => [$campaign->ozon_id],
            'objects'   => [$responseObjects->list[0]->id],
            'dateFrom'  => $campaign->start_date,
            'dateTo'    => $campaign->end_date,
            'groupBy'   => 'DATE'
        ];
        $response        = $this->ozonPerformanceService->getClientStatisticsPhrases($params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertObjectHasAttribute('UUID', $response);
    }


    // TODO было отправлено изменение и по ключевым словам и по ставкам, но ничего не изменилось.
    public function testUpdateProductsBids()
    {
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ids.0'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertEquals($response->products[0]->sku, '147699403');
        $this->assertEquals($response->products[0]->bid, '0');
        $this->assertEmpty($response->products[0]->stopWords);
        //$this->assertEquals(count($response->products[0]->phrases), 1);
        $this->assertEquals($response->products[1]->sku, '147189845');
        $this->assertEquals($response->products[1]->bid, '0');
        $this->assertEmpty($response->products[1]->stopWords);
        $this->assertEquals($response->products[1]->phrases[0]->bid, '46000000');

        $params          = [
            'bids' => [
                [
                    'sku'       => '147699403',
                    'bid'       => 30 * OzonHelper::BUDGET_COEFFICIENT,
                    'groupId'   => '47277',
                    'phrases'   => [
                        ['bid' => 25 * OzonHelper::BUDGET_COEFFICIENT, 'phrase' => 'зимняя обувь']
                    ],
                    'stopWords' => [
                        'кроссовки',
                        'носки'
                    ]
                ]
            ]
        ];
        $setBidsResponse = $this->ozonPerformanceService->updateProductsBids($this->getDataKey('campaigns.ids.0'), $params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $newBidsResponse = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ids.0'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
    }


    public function testUpdateGroupBids()
    {
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ids.0'));
        $this->assertEquals($response->products[0]->groupId, '47277');
        $this->assertEquals($response->products[0]->bid, '0');
        $this->assertEmpty($response->products[0]->stopWords);
        $this->assertEquals($response->products[0]->phrases[0]->bid, '35000000');

        $params          = [
            'bids' => [
                [
                    'title'     => 'Test',
                    'phrases'   => [
                        ['bid' => 25 * OzonHelper::BUDGET_COEFFICIENT, 'phrase' => 'зимняя обувь']
                    ],
                    'stopWords' => [
                        'кроссовки',
                        'носки'
                    ]
                ]
            ]
        ];
        $setBidsResponse = $this->ozonPerformanceService->updateGroupBids($this->getDataKey('campaigns.ids.0'), '47277', $params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
    }


    public function testCreateCampaign()
    {
        $campaignResponse = $this->ozonPerformanceService->getClientCampaigns();
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $oldCampaignCount = count($campaignResponse->list);
        $newCampaignData  = [
            'title'       => 'Test',
            'fromDate'    => Carbon::tomorrow()->toDateString(),
            'toDate'      => Carbon::tomorrow()->addDay()->toDateString(),
            'dailyBudget' => 500 * OzonHelper::BUDGET_COEFFICIENT,
            'placement'   => 'PLACEMENT_PDP'
        ];
        $createResponse   = $this->ozonPerformanceService->createCampaign($newCampaignData);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $newCampaignId = (int) $createResponse->campaignId;
        $this->assertGreaterThan(1, $newCampaignId);
        // TODO не работает описание по документации получение отдельной кампании по id
        $campaignResponse = $this->ozonPerformanceService->getClientCampaigns(['campaignIds' => [$newCampaignId]]);
        $this->assertGreaterThan($oldCampaignCount, count($campaignResponse->list));
    }


    public function testActivateCampaign()
    {
        $campaign = $this->getOzonCampaign($this->getDataKey('campaigns.ids.0'));
        $this->assertEquals('CAMPAIGN_STATE_INACTIVE', $campaign->state);
        $activeResponse = $this->ozonPerformanceService->activateCampaign($this->getDataKey('campaigns.ids.0'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertEquals('CAMPAIGN_STATE_RUNNING', $activeResponse->state);
        $campaign = $this->getOzonCampaign($this->getDataKey('campaigns.ids.0'));
        $this->assertEquals('CAMPAIGN_STATE_RUNNING', $campaign->state);
        $deactivateResponse = $this->ozonPerformanceService->deactivateCampaign($this->getDataKey('campaigns.ids.0'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertEquals('CAMPAIGN_STATE_INACTIVE', $deactivateResponse->state);
        $campaign = $this->getOzonCampaign($this->getDataKey('campaigns.ids.0'));
        $this->assertEquals('CAMPAIGN_STATE_INACTIVE', $campaign->state);
    }


    public function testProductsToCampaign()
    {
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertEmpty($response->products);
        $startCountProducts = count($response->products);
        $params             = [
            'bids' => [
                [
                    'sku'       => $this->getDataKey('campaigns.ozon_products.sku.2'),
                    'bid'       => 31 * OzonHelper::BUDGET_COEFFICIENT,
                    'stopWords' => ['дешевый']
                ]
            ]
        ];
        $response           = $this->ozonPerformanceService->addProductsToCampaign($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'), $params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertGreaterThan($startCountProducts, count($response->products));
        $this->assertEquals($response->products[0]->bid, 31 * OzonHelper::BUDGET_COEFFICIENT);
        $params['bids'][0]['bid'] = 34 * OzonHelper::BUDGET_COEFFICIENT;
        $response                 = $this->ozonPerformanceService->updateWordsToCampaignProducts($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'),
            $params);
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'));
        $this->assertEquals($response->products[0]->bid, 34 * OzonHelper::BUDGET_COEFFICIENT);
        $response = $this->ozonPerformanceService->removeProductFromCampaign(
            $this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'),
            ['sku' => [$this->getDataKey('campaigns.ozon_products.sku.2')]]
        );
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $response = $this->ozonPerformanceService->getCampaignProducts($this->getDataKey('campaigns.ozon_statistic_campaigns.ids.1'));
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertEquals($startCountProducts, count($response->products));
    }


    public function testUpdateCampaign()
    {
        $response        = $this->ozonPerformanceService->getClientCampaigns();
        $oldCampaignData = $response->list[0];
        $response        = $this->ozonPerformanceService->updateCampaign(
            $oldCampaignData->id,
            [
                'fromDate'    => Carbon::tomorrow()->toDateString(),
                'toDate'      => Carbon::tomorrow()->addDay()->toDateString(),
                'dailyBudget' => (($oldCampaignData->dailyBudget / OzonHelper::BUDGET_COEFFICIENT) + 1) * OzonHelper::BUDGET_COEFFICIENT,
            ]
        );
        $this->assertEmpty($this->ozonPerformanceService->getLastError());
        $this->assertNotEmpty($response->campaignId);
        $response        = $this->ozonPerformanceService->getClientCampaigns();
        $newCampaignData = $response->list[0];
        $this->assertEquals($oldCampaignData->id, $newCampaignData->id);
        $this->assertGreaterThan($newCampaignData->dailyBudget, $oldCampaignData->dailyBudget);
    }


    protected function setUp(): void
    {
        parent::setUp();
//        $account = new AccountDTO([
//            'id'                 => $this->getDataKey('accounts.performance.id'),
//            'platform_id'        => $this->getDataKey('accounts.performance.platform_id'),
//            'platform_client_id' => $this->getDataKey('accounts.performance.platform_client_id'),
//            'platform_api_key'   => $this->getDataKey('accounts.performance.platform_api_key'),
//            'platform'           => ['api_url' => 'https://performance.ozon.ru/api']
//        ]);

        $account = UserService::getWabAccount($this->getDataKey('accounts.performance.id'));

        $this->ozonPerformanceService = OzonPerformanceService::connect($account);
    }


    protected function getOzonCampaign($campaignId)
    {
        $campaignList = $this->ozonPerformanceService->getClientCampaigns();

        return (new Collection($campaignList->list))->first(function ($value) use ($campaignId) {
            return $value->id === $campaignId;
        });
    }
}
