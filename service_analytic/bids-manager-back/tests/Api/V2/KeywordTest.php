<?php

namespace Tests\Api\V2;

use Tests\TestCase;

class KeywordTest extends TestCase
{
    /**
     * Test keywords list method.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/keywords', config('app.url')),
            array_merge(
                $this->getRequestUserData(),
                ['campaign_good_id' => $this->getDataKey('campaigns.campaign_good_id.1')]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data' => ['*' => ['id', 'name', 'popularity']],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links' => ['*' => ['url', 'label', 'active']],
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
            'errors',
            'success'
        ]);
    }

    public function testSearch()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/v2/keywords', config('app.url')),
            array_merge(
                $this->getRequestUserData(),
                [
                    'search' => 'шампу',
                    'campaign_good_id' => $this->getDataKey('campaigns.campaign_good_id.1')
                ]
            ),
            $this->getRequestHeader()
        );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'name', 'popularity']],
            'current_page',
            'errors',
            'success'
        ]);
    }
}
