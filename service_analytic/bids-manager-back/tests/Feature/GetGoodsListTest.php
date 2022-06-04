<?php

namespace Tests\Feature;

use App\Models\CampaignProduct;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\UserForTest;

class GetGoodsListTest extends TestCase
{
    use UserForTest;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $accountId = $this->getDataKey('accounts.performance.id');
        $goods = CampaignProduct::whereHas('campaign', function (Builder $query) use ($accountId) {
            $query->where([
                ['account_id', $accountId],
                ['campaign_status_id', Status::ACTIVE],
            ]);
        })
            ->groupBy('good_id')
            ->join('campaign_good_statistics', 'campaign_good_statistics.campaign_good_id', 'campaign_goods.id')
            ->select('good_id', 'campaign_id')
            ->selectRaw('MAX(campaign_good_statistics.date) AS max_date, MIN(campaign_good_statistics.date) AS min_date')
            ->take(3)
            ->get();

        $response = $this->json('GET', config('app.url').'/api/get-goods-list-front', [
            'api_token' => $this->getToken(),
            'from' => $goods->pluck('min_date')->min(),
            'to'            => $goods->pluck('max_date')->max(),
            'campaigns'     => $goods->pluck('campaign_id')->unique(),
            'statuses'      => [Status::ACTIVE],
            'goods'         => $goods->pluck('good_id'),
            'price_from'    => 1,
            'price_to'      => 999999
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'campaignNames',
                'campaigns',
                'goods' => [
                    'counters' => [
                        'acos',
                        'avg_1000_shows_price',
                        'avg_click_price',
                        'clicks',
                        'cost',
                        'cpo',
                        'ctr',
                        'orders',
                        'popularity',
                        'profit',
                        'purchased_shows',
                        'shows'
                    ],
                    'current_page',
                    'data' => [
                        '*' => [
                            'acos',
                            'avg_1000_shows_price',
                            'avg_click_price',
                            'campaign_id',
                            'category_id',
                            'category_name',
                            'clicks',
                            'cost',
                            'cpo',
                            'ctr',
                            'good_id',
                            'good_name',
                            'goods_count',
                            'group_id',
                            'group_name',
                            'id',
                            'orders',
                            'popularity',
                            'price',
                            'profit',
                            'purchased_shows',
                            'shows',
                            'sku',
                            'status_id',
                            'status_name'
                        ]
                    ],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ],
                'xlsLink'
            ],
            'errors',
            'success'
        ]);
    }
}
