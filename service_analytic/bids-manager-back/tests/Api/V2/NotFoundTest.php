<?php

namespace Tests\Api\V2;

use Tests\TestCase;

class NotFoundTest extends TestCase
{
    /**
     * Test not found binding model response.
     */
    public function testBindingModelNotFound()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/V2/campaign/%d/groups', config('app.url'), 0),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );

        $response->assertNotFound();
        $response->assertJsonStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced'
            ]
        ]);
        $response->assertJsonFragment([
            'message' => 'Не найдена запись с идентификатором 0'
        ]);
    }

    /**
     * Test URL not found response
     */
    public function testNotFoundUrl()
    {
        $response = $this->json(
            'GET',
            sprintf('%s/api/V2/campaign//groups', config('app.url')),
            $this->getRequestUserData(),
            $this->getRequestHeader()
        );

        $response->assertNotFound();
        $response->assertJsonStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced'
            ]
        ]);
        $response->assertJsonFragment([
            'message' => 'Не найдено'
        ]);
    }
}
