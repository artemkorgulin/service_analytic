<?php

namespace Tests\Feature\Words;

use App\Traits\UserForTest;
use Tests\TestCase;

class GetXLSKeywordUploadTemplateTest extends TestCase
{
    use UserForTest;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testPositive()
    {
        $response = $this->json('GET', config('app.url') . '/api/autoselect/keywords/template', [
            'api_token' => $this->getToken(),
            'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'xlsLink',
                    'xlsName'
                ],
                'errors',
            ]);
    }
}
