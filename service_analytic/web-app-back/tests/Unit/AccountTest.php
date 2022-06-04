<?php

namespace Tests\Unit;

use Tests\Traits\UserForTest;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use UserForTest;

    /**
     * Успешный запрос
     *
     * @return void
     */
    public function testGetAllAccounts()
    {
        $user = $this->getUser();
        $response = $this->actingAs($user, 'api_v1')->json('GET', '/api/v1/get-all-accounts/1', []);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'platform_id',
                    'platform_client_id',
                    'platform_api_key',
                    'title',
                    'description',
                    'is_active',
                    'params'
                ]
            ],
            'success',
            'errors'
        ]);
    }
}
