<?php

namespace Tests\Api\v1;

use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @property array $userParams
 * @property User $user
 * @property TestHttp $http
 */
class SingInTest extends TestCase
{
    use WithFaker;

    const SIGN_IN_URL = '/v1/sign-in';

    protected $userParams;
    protected $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userParams = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => 'password123456S',
        ];

        $this->user = User::factory()->create([
            'name' => $this->userParams['name'],
            'email' => $this->userParams['email'],
            'password' => Hash::make($this->userParams['password']),
        ]);

        $this->http = new TestHttp(null, false);
    }


    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->user->forceDelete();
    }

    /**
     * @return void
     */
    public function testSingIn()
    {
        $this->http->post(self::SIGN_IN_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_OK);
        $this->http->assertStructure(['token', 'type', 'user']);
    }

    /**
     * @return void
     */
    public function testSingInErrorEmail()
    {
        $this->userParams['email'] = 'fooBarBazSpann@exapmle.com';
        $this->http->post(self::SIGN_IN_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure(['error' => ['http_code', 'code', 'message', 'advanced']]);
        $this->assertEquals(1000, $this->http->json()['error']['code']);
        $this->assertEquals('Неверный логин или пароль', $this->http->json()['error']['message']);
    }

    /**
     * @return void
     */
    public function testSingInErrorPassword()
    {
        $this->userParams['password'] = 'fooBarBazSpann123123!';

        $this->http->post(self::SIGN_IN_URL, $this->userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure(['error' => ['http_code', 'code', 'message', 'advanced']]);
        $this->assertEquals(1002, $this->http->json()['error']['code']);
        $this->assertEquals('Неверный логин или пароль', $this->http->json()['error']['message']);
    }

    /**
     * @return void
     */
    public function testSingInErrorNotification()
    {
        $userParams = $this->userParams;
        $userParams['email'] = 'foo' . $userParams['email'];

        $user = User::factory()->unverified()->create([
            'name' => $userParams['name'],
            'email' => $userParams['email'],
            'password' => Hash::make($userParams['password']),
        ]);

        $this->http->post(self::SIGN_IN_URL, $userParams);

        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure(['error' => ['http_code', 'code', 'message', 'advanced']]);
        $this->assertEquals(1001, $this->http->json()['error']['code']);
        $this->assertEquals('Вы не подтвердили адрес электронной почты', $this->http->json()['error']['message']);

        $user->forceDelete();
    }
}
