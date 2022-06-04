<?php

namespace Tests\Api\V2\Campaign;

use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\Group;
use App\Models\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test group list method.
     *
     * @return void
     */
    public function testIndex()
    {
        $user = $this->getUser();
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaign/%d/groups', config('app.url'), $this->getDataKey('campaigns.ids.0')),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'groupList' => [
                    '*' => [
                        'id',
                        'created_at',
                        'name',
                        'campaign_id',
                        'ozon_id',
                        'status_id',
                        'goods' => [
                            '*' => [
                                'id',
                                'created_at',
                                'updated_at',
                                'name',
                                'sku',
                                'product_id',
                                'offer_id',
                                'category_id',
                                'good_status_id',
                                'price',
                                'visible',
                                'account_id'
                            ]
                        ]
                    ]
                ]
            ],
            'errors',
            'success'
        ]);
    }

    public function testShow()
    {
        $user = $this->getUser();
        $firstCampaign = Campaign::where([['user_id', '=', $user['id']], ['account_id', '=', $this->getDataKey('accounts.performance.id')]])->first();
        $firstGroup = Group::where('campaign_id', '=', $firstCampaign->id)->first();
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaign/%d/groups/%s', config('app.url'), $firstCampaign->id, $firstGroup->id),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'created_at',
                'name',
                'campaign_id',
                'ozon_id',
                'status_id',
                'goods' => [
                    '*' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'name',
                        'sku',
                        'product_id',
                        'offer_id',
                        'category_id',
                        'good_status_id',
                        'price',
                        'visible',
                        'account_id'
                    ]
                ]
            ],
            'errors',
            'success'
        ]);
    }

    public function testStore()
    {
        $user = $this->getUser();
        $firstCampaign = Campaign::where([
            ['user_id', '=', $user['id']], ['account_id', '=', $this->getDataKey('accounts.performance.id')]
        ])->first();
        $goodsIds = CampaignProduct::where('campaign_id', '=', $firstCampaign->id)->get()->pluck('good_id')->all();
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/groups', config('app.url'), $firstCampaign->id),
            array_merge($this->getRequestUserData(), ['name' => 'Test_Post_123', 'goods' => $goodsIds]),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['group_id'],
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $groupId = $response['data']['group_id'];
        $countInTable = CampaignProduct::where('group_id', '=', $groupId)->whereIn('good_id', $goodsIds)->count();
        $this->assertEquals(count($goodsIds), $countInTable);
    }

    public function testDelete()
    {
        $user = $this->getUser();
        $firstCampaign = Campaign::where([['user_id', '=', $user['id']], ['account_id', '=', $this->getDataKey('accounts.performance.id')]])->first();
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/groups', config('app.url'), $firstCampaign->id),
            array_merge($this->getRequestUserData(), ['name' => 'Test_Post_123']),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response = json_decode($response->content(), true);
        $groupId = $response['data']['group_id'];

        $response = $this->json(
            'DELETE',
            sprintf('%s/api/v2/campaign/%d/groups/%d', config('app.url'), $firstCampaign->id, $groupId),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['group_id'],
            'errors',
            'success'
        ]);
        $group = Group::find($groupId);
        $this->assertEquals($group->status_id, Status::DELETED);
    }
}
