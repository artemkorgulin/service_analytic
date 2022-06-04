<?php

namespace Tests\Feature\InnerServices;

use Tests\Traits\UserForTest;
use Tests\TestCase;

class EventMasterTest extends TestCase
{
    use UserForTest;

    /**
     * Успешный запрос
     *
     * @return void
     */
    public function testGetNotifications()
    {
        $user = $this->getUser();
        $response = $this->actingAs($user, 'api_v1')->json('GET', '/api/event/v1/notifications', []);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'success',
            'errors'
        ]);
    }

    /**
     * Ошибка подключения
     *
     * @return void
     */
    public function testFailRequest()
    {
        $response = $this->json('GET', '/api/event/v1/notifications', []);
        $response->assertStatus(401);
        $response->assertUnauthorized();
        $response->assertSee('Unauthenticated.');
    }

    /**
     * Успешный запрос
     *
     * @return void
     */
    public function testFailPostNotification()
    {
        $user = $this->getUser();
        $response = $this->actingAs($user, 'api_v1')->json('POST', '/api/event/v1/notifications', ['users' => []]);
        $response->assertStatus(422);
    }
}
