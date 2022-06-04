<?php

namespace Tests\Unit;

use Tests\Traits\UserForTest;
use Tests\TestCase;

class InnerApiTest extends TestCase
{
    use UserForTest;

    /**
     * Успешный запрос
     *
     * @return void
     */
    public function testGetAllAccounts()
    {
        $response = $this->json('GET', '/api/inner/get-all-accounts/1', [], $this->getInnerRequestHeader());
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
