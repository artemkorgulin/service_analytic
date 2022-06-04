<?php

namespace Tests\Api\V2\Campaign;

use App\Models\Status;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampaignKeywordTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test keywords list method.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaign/%d/keywords', config('app.url'), $this->getDataKey('campaigns.keywords.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                ['campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'keyword_id',
                    'id',
                    'name',
                    'status_id',
                    'campaign_good_id',
                    'group_id',
                    'bid',
                    'popularity'
                ]
            ],
            'errors',
            'success'
        ]);
    }

    /**
     * Test add keywords method.
     *
     * @return void
     */
    public function testStore()
    {
        $newName = 'weurhy02348723hrwjkqh239084y23h4uio234y23u4h';
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/keywords', config('app.url'), $this->getDataKey('campaigns.keywords.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'keywords' => [
                        [
                            'keyword_id' => 3,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 42
                        ],
                        [
                            'keyword_id' => 4,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 43
                        ],
                        [
                            'keyword_id' => 6,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 44
                        ],
                        // Test create new keyword and bind from campaign product
                        [
                            'keyword_name' => $newName,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 49
                        ],
                        // Test add new keyword from group
                        [
                            'keyword_id' => 6,
                            'group_id' => $this->getDataKey('campaigns.keywords.group_id'),
                            'bid' => 44
                        ],
                        // Test create new keyword and bind from campaign group
                        [
                            'keyword_name' => $newName,
                            'group_id' => $this->getDataKey('campaigns.keywords.group_id'),
                            'bid' => 49
                        ],
                    ]
                ]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['missed_keywords']]);
        $newKeywords = DB::table('campaign_keywords')
            ->where(function (Builder $query) {
                $query->orWhere('campaign_keywords.group_id', '=', $this->getDataKey('campaigns.keywords.group_id'))
                    ->orWhere('campaign_keywords.campaign_good_id', '=', $this->getDataKey('campaigns.keywords.campaign_good_id'));
            })
            ->whereIn('keyword_id', [3, 4, 6]);
        $count = $newKeywords->count();
        $this->assertGreaterThanOrEqual(3, $count);
    }

    /**
     * Test destroy keywords method.
     *
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/keywords', config('app.url'), $this->getDataKey('campaigns.keywords.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'keywords' => [
                        [
                            'keyword_id' => 3,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 40
                        ],
                        [
                            'keyword_id' => 4,
                            'campaign_good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id'),
                            'bid' => 40
                        ]
                    ]
                ]
            ),
            $this->getRequestHeader()
        );
        $newKeywords = DB::table('campaign_keywords')
            ->where('campaign_good_id', '=', $this->getDataKey('campaigns.keywords.campaign_good_id'))
            ->whereIn('keyword_id', [3, 4]);
        $count = $newKeywords->where('status_id', '=', Status::ACTIVE)->count();
        $this->assertEquals(2, $count);
        $response = $this->json(
            'DELETE',
            sprintf('%s/api/v2/campaign/%d/keywords', config('app.url'), $this->getDataKey('campaigns.keywords.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                ['keyword_ids' => [3, 4], 'good_id' => $this->getDataKey('campaigns.keywords.campaign_good_id')]
            ),
            $this->getRequestHeader()
        );

        $response->assertJsonStructure([
            'data' => [
                    'keyword_ids',
                    'updated_count'
            ],
            'errors',
            'success'
        ]);
    }

    /**
     * Test update bid keywords method.
     *
     * @return void
     */
    public function testUpdateBid()
    {
        $response = $this->json(
            'PUT',
            sprintf('%s/api/v2/campaign/%d/keywords', config('app.url'), $this->getDataKey('campaigns.keywords.campaign_id')),
            array_merge($this->getRequestUserData(), ['keyword_ids' => [91763, 91764, 91765], 'bid' => 48]),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['updated_count'], 'errors', 'success']);
    }
}
