<?php

namespace Tests\Api\v1;

use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @property array $userParams
 * @property TestHttp $http
 */
class SingUpTest extends TestCase
{
    use WithFaker;

    const SIGN_UP_URL = '/v1/sign-up';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userParams = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => '7926111111111111',
            'password' => 'password123456S',
        ];

        $this->http = new TestHttp(null, false);
    }

    /**
     * @return void
     */
    public function testSingUp()
    {
        $this->http->post(self::SIGN_UP_URL, $this->userParams);
        $this->http->assertStatus(Response::HTTP_OK);
        $this->http->assertStructure(['success', 'data', 'errors']);
        $this->assertEquals([], $this->http->json('errors'));
        $this->assertEquals([], $this->http->json('data'));

        $user = User::query()->where('email', $this->userParams['email'])->first();

        $this->assertTrue($user->exists());

        $user->forceDelete();
    }


    /**
     * @return void
     */
    public function testSingUpErrorRequiredFields()
    {
        $requiredFieldsWithMessageKeyValue = [
            'name' => 'Имя',
            'email' => 'E-Mail адрес',
            'password' => 'Пароль',
        ];

        foreach ($requiredFieldsWithMessageKeyValue as $key => $field) {
            $paramsRequest = $this->userParams;
            unset($paramsRequest[$key]);
            $this->http->post(self::SIGN_UP_URL, $paramsRequest);
            $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->http->assertStructure([
                'error' => [
                    'http_code',
                    'code',
                    'message',
                    'advanced' => [
                        [$key]
                    ]
                ]
            ]);

            $this->assertEquals('Ошибка валидации', $this->http->json('error')['message']);
            $this->assertEquals(
                'Поле ' . $field . ' обязательно для заполнения.',
                $this->http->json('error')['advanced'][0][$key]
            );
        }
    }

    /**
     * @return void
     */
    public function testSingUpErrorEmailValidation()
    {
        $this->userParams['email'] = 'fooBarBaz';

        $this->http->post(self::SIGN_UP_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    ['email']
                ]
            ]
        ]);

        $this->assertEquals('Ошибка валидации', $this->http->json('error')['message']);
        $this->assertEquals(
            'Поле E-Mail адрес должно быть действительным электронным адресом.',
            $this->http->json('error')['advanced'][0]['email']
        );
    }

    /**
     * @return void
     */
    public function testSingUpErrorPasswordValidation()
    {
        $this->userParams['password'] = 'fooBarBaz';
        $this->http->post(self::SIGN_UP_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    ['password']
                ]
            ]
        ]);

        $this->assertEquals('Ошибка валидации', $this->http->json('error')['message']);
        $this->assertEquals(
            'Пароль обязательно должен содержать одну заглавную, одну прописную букву латинского алфавита, а также одну цифру',
            $this->http->json('error')['advanced'][0]['password']
        );
    }

    /**
     * @return void
     */
    public function testSingUpErrorEmailDuplication()
    {
        $this->http->post(self::SIGN_UP_URL, $this->userParams);

        $user = User::query()->where('email', $this->userParams['email'])->first();
        $this->assertTrue($user->exists());

        $this->http->post(self::SIGN_UP_URL, $this->userParams);
        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    ['email']
                ]
            ]
        ]);

        $this->assertEquals('Ошибка валидации', $this->http->json('error')['message']);
        $this->assertEquals(
            'Пользователь с таким email уже зарегистрирован в системе',
            $this->http->json('error')['advanced'][0]['email']
        );

        $user->forceDelete();
    }
}
