<?php

namespace Tests\Feature;

use App\Traits\UserForTest;
use Tests\TestCase;

class GetStrategiesListTest extends TestCase
{
    use UserForTest;
    /**
     * Get campaigns list
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->json('GET', config('app.url') . '/api/get-strategies-list', [
            'api_token' => $this->getToken()
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'errors',
                    'data' => [
                        'strategies' => [
                            'current_page',
                            'data' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'behavior',
                                    'strategies_all',
                                    'strategies_active'
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
                        ]
                    ],
                ]);
    }
}
