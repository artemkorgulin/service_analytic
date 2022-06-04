<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;

use Tests\TestCase;
use App\Models\User;

use App\Services\PhoneService;

/**
 * @property array $userParams
 * @property User $user
 * @property TestHttp $http
 */
class PhoneLoginTest extends TestCase
{
    use WithFaker;

    public const SIGN_UP_URL = '/v1/sign-up';
    public const SIGN_IN_URL = '/v1/sign-in';
    public const SEND_CODE_URL = '/v1/phone/send-code';
    public const VERIFY_CODE_URL = '/v1/phone/confirm';

    public const TEST_NAME = "Test Testovich";
    public const TEST_PASSWORD = "abcd12345A";
    public const TEST_EMAIL = "verytestuserforunittest@sellerexpert.ru";
    public const TEST_PHONE = "79269215196";

    public const SECOND_TEST_NAME = "Second Test Testovich";
    public const SECOND_TEST_PASSWORD = "abcd12345A";
    public const SECOND_TEST_EMAIL = "verytestuserforunittest2@sellerexpert.ru";
    public const SECOND_TEST_PHONE = "7926111111111111"; # таких длинных у нас на руси нет телефонов

    protected $phoneService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearTestUsersFromDatabase();
        $this->http = new TestHttp(null, false);
        $this->phoneService = new PhoneService();
    }


    /**
     * Можно зарегистрироваться указав телефон
     */
    public function testSignUpWithPhone()
    {
        $this->assertDatabaseMissing('users', [
            'unverified_phone' => self::TEST_PHONE
        ]);

        $this->registerFirstUser();

        $this->assertGoodResponse();

        $this->assertDatabaseHas('users', [
            'unverified_phone' => self::TEST_PHONE
        ]);
    }

    /**
     * Если кто-то занял такой телефон - то регистрация не пройдёт
     */
    public function testCannotRegisterWithSamePhone()
    {
        $this->assertDatabaseMissing('users', [
            'unverified_phone' => self::TEST_PHONE
        ]);

        $this->registerFirstUser(true);

        $secondUser = $this->registerUser([
            "name" => self::SECOND_TEST_NAME,
            "email" => self::SECOND_TEST_EMAIL,
            "phone" => self::TEST_PHONE,
            "password" => self::SECOND_TEST_PASSWORD,
        ]);

        $this->assertBadResponse('phone', 'Такое значение поля Телефон уже существует.');
    }

    /**
     * Телефон обязателен, если мы включили новую механику
     */
    public function testPhoneRequiredForRegistration()
    {
        $firstUser = $this->registerUser([
            "name" => self::TEST_NAME,
            "email" => self::TEST_EMAIL,
            "password" => self::TEST_PASSWORD,
        ]);

        $this->assertBadResponse('phone', 'Поле Телефон обязательно для заполнения.');
    }


    /**
     * Проверяем что телефон обязателен для запроса кода
     */
    public function testPhoneRequiredToSendCode()
    {
        $user = $this->registerFirstUser();

        $this->http->post(self::SEND_CODE_URL, [ ]);

        $this->assertBadResponse('phone', 'Поле Телефон обязательно для заполнения.');
    }

    /**
     * Нельзя отправить код на неизвестный номер, что не проходил регистрацию или не задавал в настройках
     */
    public function testSendOnlyToKnownNumbers()
    {
        $this->sendCodeTo(self::TEST_PHONE);
        $this->assertBadResponse('phone', 'Данный телефон не запрашивал подтвержения');
    }

    /**
     * Если телефон верифицировали - то на него код тоже нельзя запросить пока
     */
    public function testSendOnlyToUnverifiedPhone()
    {
        $user = $this->registerFirstUser(true);

        $this->sendCodeTo(self::TEST_PHONE);

        $this->assertBadResponse('phone', 'Такое значение поля Телефон уже существует.');
    }

    /**
     * Код должен быть только под нужный номер телефона
     */
    public function testCannotConfirmWithOtherUserCode()
    {
        $this->registerFirstUser();
        $this->registerSecondUser();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->sendCodeTo(self::SECOND_TEST_PHONE);

        $this->confirmPhone(self::TEST_PHONE, $this->secondUser()->phone_verification_token);
        $this->assertBadResponse('token', 'Токен недействителен');
    }

    /**
     * Нельзя верифицировать телефон случайным кодом
     */
    public function testCannotVerifyWithRandomCode()
    {
        $this->registerFirstUser();

        $this->sendCodeTo(self::TEST_PHONE);

        $this->confirmPhone(self::TEST_PHONE, Str::random());
        $this->assertBadResponse('token', 'Токен недействителен');
    }

    /**
     * Нельзя верифицировать телефон устаревшим кодом
     */
    public function testVerificationCodeHaveLifetime()
    {
        $this->registerFirstUser();
        $this->sendCodeTo(self::TEST_PHONE);
        $user = $this->firstUser();
        $user->phone_verification_token_created_at = Carbon::now()->subSeconds(PhoneService::PHONE_TOKEN_LIFETIME + 1);
        $user->save();
        $this->confirmPhone(self::TEST_PHONE, $user->phone_verification_token);
        $this->assertBadResponse('token', 'Токен устарел');
    }


    /**
     * Необходимо подождать перед выпуском следующего кода
     */
    public function testMustWaitBeforeNextCode()
    {
        $this->registerFirstUser();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->assertGoodResponse();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->assertBadResponse('phone', 'Необходимо выждать время перед запросом нового кода');

        $user = $this->firstUser();
        $user->phone_verification_token_created_at = Carbon::now()
             ->subSeconds(PhoneService::PHONE_TOKEN_RESEND_TIME + 1);
        $user->save();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->assertGoodResponse();
    }

    /**
     * После смены телефона - будет другой уже код
     */
    public function testVerificationCodeChangedAfterSet()
    {
        $user = $this->registerFirstUser();
        $this->sendCodeTo(self::TEST_PHONE);

        $firstToken = $user->fresh()->phone_verification_token;

        $this->loginWithFirstUser();
        $this->setUserPhone(self::SECOND_TEST_PHONE);

        $this->logout();

        $user = $user->fresh();
        $user->phone_verification_token_created_at = Carbon::now()
             ->subSeconds(PhoneService::PHONE_TOKEN_RESEND_TIME + 1);
        $user->save();

        $this->sendCodeTo(self::SECOND_TEST_PHONE);

        $secondToken = $user->fresh()->phone_verification_token;

        $this->assertNotEquals($firstToken, $secondToken);
    }



    /**
     * Получая информацию о себе - видно неподтверждённый телефон
     */
    public function testHaveUnverifiedPhoneInResponse()
    {
        $this->registerFirstUser();
        $this->loginWithFirstUser();
        $this->http->get('/v1/me');
        $this->assertGoodResponse();
        $this->http->assertStructure([
            'data' => [
                'user' => [
                    'phone',
                    'unverified_phone'
                ]
            ]
        ]);
        $this->assertEquals(self::TEST_PHONE, $this->http->json()['data']['user']['unverified_phone']);
    }

    /**
     * Получая настройки - тоже виден неподтверждённый телефон
     */
    public function testUnverifiedPhoneInSettingsResponse()
    {
        $this->registerFirstUser();
        $this->loginWithFirstUser();
        $this->http->get('/v1/get-settings');
        $this->assertGoodResponse();
        $this->http->assertStructure([
            'data' => [
                'user' => [
                    'phone',
                    'unverified_phone'
                ]
            ]
        ]);
        $this->assertEquals(self::TEST_PHONE, $this->http->json()['data']['user']['unverified_phone']);
    }

    /**
     * Можно вообще задавать телефон в настройках
     */
    public function testCanSetPhoneInSettings()
    {
        $this->registerFirstUser();
        $this->loginWithFirstUser();
        $this->getInfo();
        $this->assertEquals(self::TEST_PHONE, $this->http->json()['data']['user']['unverified_phone']);

        $this->setUserPhone(self::SECOND_TEST_PHONE);

        $this->getInfo();
        $this->assertEquals(self::SECOND_TEST_PHONE, $this->http->json()['data']['user']['unverified_phone']);
    }

    /**
     * Сразу после регистрации - для входа номер не доступен
     */
    public function testCannotLoginWithJustRegisteredPhone()
    {
        $this->registerFirstUser();

        $this->http->post('/v1/sign-in', ['phone'=>self::TEST_PHONE, 'password'=>self::TEST_PASSWORD]);

        $this->assertBadLogin();
    }

    /**
     * После верификации - можно зайти
     */
    public function testCanLoginAfterCodeVerification()
    {
        $user = $this->registerFirstUser();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->confirmPhone(self::TEST_PHONE, $user->fresh()->phone_verification_token);

        $this->http->post('/v1/sign-in', ['phone'=>self::TEST_PHONE, 'password'=>self::TEST_PASSWORD]);

        $this->assertGoodLogin();
    }

    /**
     * Если телефон сменили - по новому нельзя войти
     */
    public function testCannotLoginAfterPhoneSetWithNewPhone()
    {
        $user = $this->registerFirstUser();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->confirmPhone(self::TEST_PHONE, $user->fresh()->phone_verification_token);

        $this->loginWithFirstUser();
        $this->setUserPhone(self::SECOND_TEST_PHONE);

        $this->logout();
        $this->http->post('/v1/sign-in', ['phone'=>self::SECOND_TEST_PHONE, 'password'=>self::TEST_PASSWORD]);

        $this->assertBadLogin();
    }

    /**
     * Можно войти по старому телефону, если есть новый неподтверждённый
     */
    public function testCanLoginAfterPhoneChangeWithOldPhone()
    {
        $user = $this->registerFirstUser();

        $this->sendCodeTo(self::TEST_PHONE);
        $this->confirmPhone(self::TEST_PHONE, $user->fresh()->phone_verification_token);

        $this->loginWithFirstUser();
        $this->setUserPhone(self::SECOND_TEST_PHONE);

        $this->logout();
        $this->http->post('/v1/sign-in', ['phone'=>self::TEST_PHONE, 'password'=>self::TEST_PASSWORD]);

        $this->assertGoodLogin();
    }

    /**
     * Войти под первым пользователем
     */
    private function loginWithFirstUser()
    {
        $this->http = new TestHttp(null, true, self::TEST_EMAIL, self::TEST_PASSWORD);
    }

    /**
     * Перезайти под гостем
     */
    private function logout()
    {
        $this->http = new TestHttp(null, true);
    }

    /**
     * Через настройки задать пользователю телефон новый
     */
    private function setUserPhone($phone)
    {
        $this->http->post('/v1/set-settings', compact('phone'));
    }

    /**
     * Получить информацию о себе
     */
    private function getInfo()
    {
        $this->http->get('/v1/me');
    }

    /**
     * Проверка что авторизация прошла успешно
     */
    private function assertGoodLogin()
    {
        $this->http->assertStatus(Response::HTTP_OK);
        $this->http->assertStructure([
            'token',
            'type',
            'user'
        ]);
    }

    /**
     * Проверка что произошла ошибка при попытке войти
     */
    private function assertBadLogin()
    {
        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('Неверный логин или пароль', $this->http->json('error')['message']);
    }

    /**
     * Удалить тестовых пользователей из базы
     */
    private function clearTestUsersFromDatabase()
    {
        User::query()->where('phone', self::TEST_PHONE)->forceDelete();
        User::query()->where('email', self::TEST_EMAIL)->forceDelete();

        User::query()->where('phone', self::SECOND_TEST_PHONE)->forceDelete();
        User::query()->where('email', self::SECOND_TEST_EMAIL)->forceDelete();
    }

    /**
     * Преобразовать параметры регистрации в поиск по пользователям
     */
    private function findUserByParams($params)
    {
        if (isset($params['phone'])) {
            $params['unverified_phone'] = $params['phone'];
            unset($params['phone']);
        }
        unset($params['password']);
        return User::where($params)->first();
    }

    /**
     * Найти в базе свежий объект пользователя 1
     */
    private function firstUser()
    {
        return $this->findUserByParams([
            "name" => self::TEST_NAME,
            "email" => self::TEST_EMAIL,
            "phone" => self::TEST_PHONE
        ]);
    }

    /**
     * Найти в базе свежего второго пользователя
     */
    private function secondUser()
    {
        return $this->findUserByParams([
            "name" => self::SECOND_TEST_NAME,
            "email" => self::SECOND_TEST_EMAIL,
            "phone" => self::SECOND_TEST_PHONE
        ]);
    }

    /**
     * Зарегистрировать пользователя
     */
    private function registerUser($params, $verify = false)
    {
        $this->http->post(self::SIGN_UP_URL, $params);

        $user = $this->findUserByParams($params);
        if ($user) {
            $user->email_verified_at = Carbon::now();
            $user->save();
        }
        if ($verify) {
            $this->phoneService->setCurrentUserPhone($user)->save();
        }

        return $user;
    }

    /**
     * Отправить код на номер телефона
     */
    private function sendCodeTo($phone)
    {
        $this->http->post(self::SEND_CODE_URL, [ 'phone' => $phone ]);
    }

    /**
     * Подтвердить номер телефона кодом
     */
    private function confirmPhone($phone, $code)
    {
        $this->http->post(self::VERIFY_CODE_URL, [ 'phone' => $phone, 'token'=>$code ]);
    }

    /**
     * Зарегистрировать первого пользователя
     */
    private function registerFirstUser($verify = false)
    {
        return $this->registerUser([
            "name" => self::TEST_NAME,
            "email" => self::TEST_EMAIL,
            "phone" => self::TEST_PHONE,
            "password" => self::TEST_PASSWORD,
        ], $verify);
    }

    /**
     * Зарегистрировать второго пользователя
     */
    private function registerSecondUser($verify = false)
    {
        return $this->registerUser([
            "name" => self::SECOND_TEST_NAME,
            "email" => self::SECOND_TEST_EMAIL,
            "phone" => self::SECOND_TEST_PHONE,
            "password" => self::SECOND_TEST_PASSWORD,
        ], $verify);
    }

    /**
     * Проверка что последний запрос нормальный
     */
    private function assertGoodResponse()
    {
        $this->http->assertStatus(Response::HTTP_OK);
        $this->http->assertStructure([
            'success',
            'data',
            'errors'
        ]);

        $this->assertEquals([], $this->http->json('errors'));
    }

    /**
     * Проверка что в последнем запросе произошла ошибка
     */
    private function assertBadResponse($field, $msg)
    {
        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    [$field]
                ]
            ]
        ]);

        $this->assertEquals('Ошибка валидации', $this->http->json('error')['message']);
        $this->assertEquals(
            $msg,
            $this->http->json('error')['advanced'][0][$field]
        );
    }
}
