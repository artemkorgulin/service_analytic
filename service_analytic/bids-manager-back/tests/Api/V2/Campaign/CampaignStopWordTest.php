<?php

namespace Tests\Api\V2\Campaign;

use App\Models\Status;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampaignStopWordTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test stop words list method.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/campaign/%d/stop-words', config('app.url'), $this->getDataKey('campaigns.stop_words.campaign_id')),
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
                    'id',
                    'name',
                    'campaign_good_id',
                    'group_id'
                ]
            ],
            'errors',
            'success'
        ]);
    }

    /**
     * Test add stop words method.
     *
     * @return void
     */
    public function testStore()
    {
        $newName = 'зубной';
        $deleteKeywordQuery = DB::table('campaign_keywords')
            ->where('campaign_good_id', '=', $this->getDataKey('campaigns.stop_words.campaign_good_id'))
            ->where('status_id', '=', Status::DELETED);
        $deleteKeywordCount = $deleteKeywordQuery->count();
        $this->assertEquals($deleteKeywordCount, 0);
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/stop-words', config('app.url'), $this->getDataKey('campaigns.stop_words.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'stop_words' => [
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.0'),
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.1'),
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.2'),
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        // Test create new stop word and bind from campaign product
                        [
                            'stop_word_name' => $newName,
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        // Test add new stop word from group
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.2'),
                            'group_id' => $this->getDataKey('campaigns.stop_words.group_id')
                        ],
                        // Test create new stop word and bind from campaign group
                        [
                            'stop_word_name' => $newName,
                            'group_id' => $this->getDataKey('campaigns.stop_words.group_id')
                        ],
                    ]
                ]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['missed_stop_words', 'detach_keywords']]);
        $newStopWords = DB::table('campaign_stop_words')
            ->where(function (Builder $query) {
                $query->orWhere('campaign_stop_words.group_id', '=', $this->getDataKey('campaigns.stop_words.group_id'))
                    ->orWhere('campaign_stop_words.campaign_good_id', '=', $this->getDataKey('campaigns.stop_words.campaign_good_id'));
            })->whereIn('stop_word_id', $this->getDataKey('campaigns.stop_words.ids'));
        $count = $newStopWords->count();
        $this->assertGreaterThanOrEqual(3, $count);
        $deleteKeywordCount = $deleteKeywordQuery->count();
        $this->assertEquals($deleteKeywordCount, 3);
    }

    /**
     * Test destroy stop_words method.
     *
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json(
            'POST',
            sprintf('%s/api/v2/campaign/%d/stop-words', config('app.url'), $this->getDataKey('campaigns.stop_words.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'stop_words' => [
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.0'),
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.1'),
                            'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                        ],
                        [
                            'stop_word_name' => $this->getDataKey('campaigns.stop_words.names.1'),
                            'group_id' => $this->getDataKey('campaigns.stop_words.group_id')
                        ],
                    ]
                ]
            ),
            $this->getRequestHeader()
        );
        $newStopWords = DB::table('campaign_stop_words')
            ->where(function ($subQuery) {
                $subQuery->where('campaign_good_id', '=', $this->getDataKey('campaigns.stop_words.campaign_good_id'))
                    ->orWhere('group_id', '=', $this->getDataKey('campaigns.stop_words.group_id'));
            })->whereIn('stop_word_id', $this->getDataKey('campaigns.stop_words.ids'));
        $count = $newStopWords->count();
        $this->assertEquals(3, $count);
        $response = $this->json(
            'DELETE',
            sprintf('%s/api/v2/campaign/%d/stop-words', config('app.url'), $this->getDataKey('campaigns.stop_words.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'stop_word_ids' => $this->getDataKey('campaigns.stop_words.ids'),
                    'campaign_good_id' => $this->getDataKey('campaigns.stop_words.campaign_good_id')
                ]
            ),
            $this->getRequestHeader()
        );

        $response->assertJsonStructure([
            'data' => [
                    'stop_word_ids',
                    'deleted_count'
            ],
            'errors',
            'success'
        ]);
        $response = json_decode($response->content(), true);
        $this->assertEquals(2, $response['data']['deleted_count']);
        $this->assertEquals(2, count($response['data']['stop_word_ids']));

        $response = $this->json(
            'DELETE',
            sprintf('%s/api/v2/campaign/%d/stop-words', config('app.url'), $this->getDataKey('campaigns.stop_words.campaign_id')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'stop_word_ids' => $this->getDataKey('campaigns.stop_words.ids'),
                    'group_id' => $this->getDataKey('campaigns.stop_words.group_id')
                ]
            ),
            $this->getRequestHeader()
        );

        $response = json_decode($response->content(), true);
        $this->assertEquals(1, $response['data']['deleted_count']);
        $this->assertEquals(1, count($response['data']['stop_word_ids']));
    }
}
