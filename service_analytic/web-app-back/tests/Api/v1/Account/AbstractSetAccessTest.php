<?php

namespace Tests\Api\v1\Account;

use App\Models\Account;
use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @property TestHttp $http
 * @property array $userParams
 * @property \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $user
 */
abstract class AbstractSetAccessTest extends TestCase
{
    const SET_ACCESS_URL = '/v1/set-access';

    /**
     * @return void
     */
    public function testSetAccess()
    {
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_OK);
        $this->http->assertStructure([
            'errors',
            'success',
            'data' => [
                'description',
                'id',
                'max_count_request_per_day',
                'platform' => [
                    'api_url',
                    'description',
                    'id',
                    'settings',
                    'title'
                ],
                'platform_api_key',
                'platform_client_id',
                'platform_id',
                'platform_title',
                'title',
            ]
        ]);

        $response = $this->http->json('data');

        $this->assertEquals($this->userParams['platform_id'], $response['platform_id']);
        $this->assertEquals($this->userParams['platform_id'], $response['platform']['id']);
        $this->assertEquals($this->userParams['client_id'], $response['platform_client_id']);
        $this->assertEquals($this->userParams['client_api_key'], $response['platform_api_key']);
        $this->assertEquals($this->userParams['title'], $response['title']);

        $account = Account::query()->where([
            'platform_api_key' => $this->userParams['client_api_key'],
            'platform_client_id' => $this->userParams['client_id'],
            'platform_id' => $this->userParams['platform_id'],
        ]);

        $this->assertTrue($account->exists());

        $account->forceDelete();
    }

    /**
     * @return void
     */
    public function testSetAccessValidateErrorEmptyCompany()
    {
        $this->userParams['company_id'] = 1;

        $this->http->post(self::SET_ACCESS_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    ['company_id']
                ]
            ]
        ]);

        $errorArray = $this->http->json('error');

        $this->assertEquals('Ошибка валидации', $errorArray['message']);
        $this->assertEquals(
            'Пользователь не привязан к компании.',
            $errorArray['advanced'][0]['company_id']
        );
    }

    /**
     * @return void
     */
    public function testSetAccessValidateErrorApiKey()
    {
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);

        $account = Account::query()->where([
            'platform_api_key' => $this->userParams['client_api_key'],
            'platform_client_id' => $this->userParams['client_id'],
            'platform_id' => $this->userParams['platform_id'],
        ]);

        $this->userParams['client_id'] = Str::random(25);
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);
        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $errorArray = $this->http->json('error');

        $this->http->assertStructure([
            'error' => [
                'http_code',
                'advanced' => [
                    ['client_api_key']
                ]
            ]
        ]);
        $this->assertEquals(
            'API key занят другим пользователем. Использовать одинаковые аккаунты API нельзя.',
            $errorArray['advanced'][0]['client_api_key']
        );

        $this->userParams['client_id'] = Str::random(25);
        $this->userParams['client_api_key'] = 123;
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);
        $errorArray = $this->http->json('error');

        $this->assertEquals(
            'Поле client api key должно быть строкой.',
            $errorArray['advanced'][0]['client_api_key']
        );

        $account->forceDelete();
    }

    /**
     * @return void
     */
    public function testSetAccessValidateErrorClientId()
    {
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);

        $account = Account::query()->where([
            'platform_api_key' => $this->userParams['client_api_key'],
            'platform_client_id' => $this->userParams['client_id'],
            'platform_id' => $this->userParams['platform_id'],
        ]);

        $this->userParams['client_api_key'] = Str::random(25);
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);
        $errorArray = $this->http->json('error');

        $this->http->assertStructure([
            'error' => [
                'http_code',
                'advanced' => [
                    ['client_id']
                ]
            ]
        ]);
        $this->assertEquals(
            'Client ID занят другим пользователем. Использовать одинаковые аккаунты API нельзя.',
            $errorArray['advanced'][0]['client_id']
        );

        $this->userParams['client_api_key'] = Str::random(25);
        $this->userParams['client_id'] = 123;
        $this->http->post(self::SET_ACCESS_URL, $this->userParams);
        $errorArray = $this->http->json('error');

        $this->assertEquals(
            'Поле client id должно быть строкой.',
            $errorArray['advanced'][0]['client_id']
        );

        $account->forceDelete();
    }
}

