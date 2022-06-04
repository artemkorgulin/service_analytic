<?php

namespace App\Http\Controllers\Api\v1;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegistrationRequest;
use App\Http\Requests\Api\SettingsRequest;
use App\Http\Requests\Api\PhoneConfirmRequest;
use App\Http\Requests\Api\PhoneSendCodeRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Jobs\User\NewUserEmailSubscriber;
use App\Mail\PasswordResetEmail;
use App\Mail\RegistrationEmail;
use App\Models\ModelHasRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermission;
use App\Models\User;
use App\Repositories\Billing\TariffRepository;
use App\Repositories\Billing\UserTariffRepository;
use App\Services\UserService;
use App\Services\PhoneService;
use App\Services\V2\OzonApi;
use App\Services\V2\OzonPerformanceApi;
use App\Repositories\UserRepository;
use Auth;
use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Config;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


/**
 * Class AuthController
 * Контроллер с методами авторизации пользователей
 * @package App\Http\Controllers\Api\v1
 */
class AuthController extends Controller
{
    /**
     * Get the authenticated User.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return response()->api_success([$request->user()]);
    }

    /**
     * Get a JWT via given credentials.
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = ['password' => $request->password];
        if ($request->phone) {
            if (config('feature.phone-login')) {
                $user = User::where(['phone' => $request->phone])->first();
                $credentials['phone'] = $request->phone;
            } else {
                $user = null;
            }
        } else {
            $user = User::where(['email' => $request->email])->first();
            $credentials['email'] = $request->email;
        }

        if (!$user) {
            return response()->api_fail(
                'Неверный логин или пароль',
                [],
                422,
                AuthErrors::USER_NOT_FOUND
            );
        }
        if (!$user->email_verified_at) {
            return response()->api_fail(
                'Вы не подтвердили адрес электронной почты',
                [],
                422,
                AuthErrors::EMAIL_NOT_VERIFIED
            );
        }

        if (!$token = auth()->guard('api_v1')->attempt($credentials)) {
            return response()->api_fail(
                'Неверный логин или пароль',
                [],
                422,
                AuthErrors::WRONG_PASSWORD
            );
        }

        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'user' => 'bearer '.$token,
        ], 200);
    }

    /**
     * Смена ролей пользователей
     * @throws Exception
     */
    public function changeRolesUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new Exception('не передан id пользователя');
        }

        if (!$user = User::query()->find($request->userId)) {
            throw new Exception('не найден пользователь');
        }

        $obj = ModelHasRole::query()->select(['role_id'])->where([
            'model_id' => $request->userId, 'model_type' => User::class
        ])->get()->toArray();
        if (!$obj) {
            foreach ($request->roles as $role) {
                if (!$name = Role::query()->find($role)->name) {
                    throw new Exception('роль не найдена');
                }
                $user->assignRole($name);
            }
            return response()->json('Добавлены новые роли');
        }
        foreach ($obj as $item) {
            $roles_user[] = $item['role_id'];
        }

        $diff = array_diff($roles_user, $request->roles);
        if (!empty($diff)) {
            foreach ($diff as $role) {
                if (!$name = Role::query()->find($role)->name) {
                    throw new Exception('роль не найдена');
                }
                $user->removeRole($name);
            }
        }

        $diff = array_diff($request->roles, $roles_user);
        if (!empty($diff)) {
            foreach ($diff as $role) {
                if (!$name = Role::query()->find($role)->name) {
                    throw new Exception('роль не найдена');
                }
                $user->assignRole($name);
            }
        }
        return response()->json('Изменены роли пользователя');
    }


    /**
     * Add user
     * @param  Request  $request
     * @return JsonResponse
     */
    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required',
            'email' => 'required|string|email:rfc,dns',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where(['email' => $request->email])->first();
        if ($user && $user->email_verified_at) {
            return response()->json(['Пользователь с таким email уже существует'], 400);
        }

        if (!$user) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->unverified_phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->verification_token = Str::random(60);
            $user->api_token = Str::random(60);
            $user->first_login = true;
            $user->save();
        }

        $confirmRegistrationUrl = Config::get('env.front_app_url').'/email-verify?token='.$user->verification_token;
        Mail::send(new RegistrationEmail($confirmRegistrationUrl, $user));

        return response()->json($user->id);
    }

    /**
     * Получаем пользователя
     */
    public function getUser(Request $request)
    {
        if (empty($request->userId)) {
            throw new Exception('не передан id пользователя');
        }
        $user = User::query()->find($request->userId)->toArray();
        $user['roles'] = User::query()->select(['r.name', 'r.id'])
            ->join('model_has_roles as hr', function ($q) {
                $q->on('hr.model_id', '=', 'users.id')->where(['hr.model_type' => 'App\Models\User']);
            })->join('roles as r', 'r.id', '=', 'hr.role_id')->where(['users.id' => $user['id']])->get()->toArray();

        $user['platforms'] = User::query()->select(['p.id', 'p.title'])->leftJoin('user_account as ua', 'users.id', '=',
            'ua.user_id')
            ->leftJoin('accounts as a', 'a.id', '=', 'ua.account_id')
            ->leftJoin('platforms as p', 'p.id', '=', 'a.platform_id')
            ->where(['users.id' => $user['id']])->get()->toArray();

        $user['tariff'] = User::query()
            ->select(['t.id', 't.description'])
            ->leftJoin('tariff_activity as ta', 'users.id', '=', 'ta.user_id')
            ->leftJoin('tariffs as t', 't.id', '=', 'ta.tariff_id')
            ->where(['users.id' => $request->userId])
            ->first();
        return response()->json($user);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->guard('api_v1')->logout();

        return response()->api_success([], 200);
    }

    /**
     * Регистрация пользователя
     * @param  RegistrationRequest  $request
     * @return mixed
     */
    public function registration(RegistrationRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->verification_token = Str::random(60);
        $user->api_token = Str::random(60);
        $user->first_login = true;
        if (config('feature.phone-login')) {
            $user->unverified_phone = $request->phone;
        }
        $user->save();

        $confirmRegistrationUrl = Config::get('env.front_app_url').'/email-verify?token='.$user->verification_token;
        Mail::send(new RegistrationEmail($confirmRegistrationUrl, $user));

        return response()->api_success([], 200);
    }


    /**
     * Подтверждение регистрации пользователя
     * @param  Request  $request
     * @return mixed
     */
    public function confirmRegistration(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where(['verification_token' => $request->token])->first();

        if (!$user) {
            return response()->api_fail(
                'Заявка на регистрацию не найдена',
                [],
                422,
                AuthErrors::USER_NOT_FOUND
            );
        }

        if ($user->verification_token && $user->email_verified_at) {
            return response()->api_fail(
                'Пользователь уже был зарегистрирован ранее',
                [],
                422,
                AuthErrors::USER_ALREADY_REGISTERED
            );
        }

        $user->email_verified_at = Now();
        $user->verification_token = null;
        $user->save();

        try {
            NewUserEmailSubscriber::dispatch($user, request()->ip());
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        return response()->api_success([], 200);
    }


    /**
     * Запрос на сброс пароля
     * @param  Request  $request
     * @return mixed
     */
    public function resetPasswordRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where(['email' => $request->email])->first();

        if (!$user) {
            return response()->api_fail(
                'Пользователь, с указанным email, не найден',
                [],
                422,
                AuthErrors::USER_NOT_FOUND
            );
        }

        if (!$user->email_verified_at) {
            return response()->api_fail(
                'Пользователь, с указанным email, не найден',
                [],
                422,
                AuthErrors::EMAIL_NOT_VERIFIED
            );
        }

        $user->verification_token = Str::random(60);
        $user->save();

        $confirmResetPasswordUrl = Config::get('env.front_app_url').'/password-recover?token='.$user->verification_token;
        Mail::send(new PasswordResetEmail($confirmResetPasswordUrl, $user));

        return response()->api_success([], 200);
    }


    /**
     * Сброс пароля
     * @param  Request  $request
     * @return mixed
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        $user = User::where(['verification_token' => $request->token])->first();

        if (!$user) {
            return response()->api_fail(
                'Заявка на смену пароля отсутствует',
                [],
                422,
                AuthErrors::USER_NOT_FOUND
            );
        }

        $user->password = Hash::make($request->password);
        $user->verification_token = null;
        $user->save();

        return response()->api_success([], 200);
    }

    /**
     * Смена пароля
     * @param  Request  $request
     * @return mixed
     */
    public function password(Request $request)
    {
        $request->validate([
            'new_password' => 'string|min:8',
            'old_password' => 'string|min:8'
        ]);

        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->api_success([]);
        } else {
            return response()->api_fail(
                'Неверный текущий пароль',
                [],
                422,
            );
        }
    }

    /**
     * Установка ключей озона для пользователя
     * @param  SettingsRequest  $request
     * @return mixed
     */
    public function setSettings(SettingsRequest $request, UserService $userService, PhoneService $phoneService)
    {
        $user = Auth::user();

        //валидация ozon api ключей
        if (($request->ozon_client_id || $request->ozon_client_api_key) && !(new OzonApi($request->ozon_client_id,
                $request->ozon_client_api_key))->validateAccessData()) {
            return response()->api_fail(
                'Ошибка подключения к Ozon API, проверьте правильность введенных данных',
                [],
                422
            );
        }

        //валидация ozon performance ключей
        if (!$this->isOzonPerformanceKeysValid($request->ozon_supplier_id, $request->ozon_supplier_api_key)) {
            return response()->api_fail(
                'Ошибка подключения, проверьте правильность введенных данных',
                [],
                422
            );
        }

        $data = $request->all();
        $updateData = [
            'enable_email_notifications',
            'name'
        ];
        $data = array_filter($data, fn($key) => in_array($key, $updateData), ARRAY_FILTER_USE_KEY);
        $user->update($data);

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        if (!empty($request->phone) && $phoneService->isNewPhone($user, $request->phone)) {
            $user->unverified_phone = $request->phone;
        }

        if (!empty($request->tariff_phone_modal_shown)) {
            $user->tariff_phone_modal_shown = $request->tariff_phone_modal_shown;
            $user->tariff_phone_modal_shown_at = $request->tariff_phone_modal_shown ? Carbon::now() : null;
        }

        $user->save();

        if (!empty($request->email) && $user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
            $user->verification_token = Str::random(60);
            $user->save();

            $confirmRegistrationUrl = Config::get('env.front_app_url').'/email-verify?token='.$user->verification_token;
            Mail::send(new RegistrationEmail($confirmRegistrationUrl, $user));
        }

        $userService->forgetAllCache();

        return response()->api_success(['status' => 'success', 'name' => $user->name], 200);
    }

    /**
     * Условие валидации ozon performance ключей
     * @param $ozon_supplier_id
     * @param $ozon_supplier_api_key
     * @return bool
     */
    private function isOzonPerformanceKeysValid($ozon_supplier_id, $ozon_supplier_api_key): bool
    {
        //если отправили пустые ключи, то сбросим их
        if (!($ozon_supplier_id || $ozon_supplier_api_key)) {
            return true;
        }

        $service = new OzonPerformanceApi();

        return ($ozon_supplier_id || $ozon_supplier_api_key)
            && ($service)->validatePerformanceKeys($ozon_supplier_id, $ozon_supplier_api_key);
    }

    /**
     * Получение настроек пользователя
     * @return mixed
     */
    public function getSettings(Request $request)
    {
        return self::me($request, new UserService(), new UserTariffRepository());
    }

    /**
     * Информация о пользователе
     * @param  Request  $request
     * @return mixed
     */
    public function me(Request $request, UserService $userService, UserTariffRepository $userTariffRepository)
    {
        $user = auth()->user();
        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'email' => $user->email,
                'unverified_phone' => $user->unverified_phone,
                'phone' => $user->phone,
                'phone_verified_at' => $user->phone_verified_at,
                'email_verified_at' => $user->email_verified_at,

                'tariff_phone_modal_shown' => $user->tariff_phone_modal_shown,
                'tariff_phone_modal_shown_at' => $user->tariff_phone_modal_shown_at,

                'api_token' => $user->api_token,
                'inn' => $user->inn,
                'accounts' => $userService->setAccountsCache('me'),
                'tariffs' => $userTariffRepository->getActualTariff($user),
                'companies' => $userService->setCompaniesCache(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'enable_email_notifications' => $user->enable_email_notifications,
                'first_login' => $user->first_login,
            ]
        ];

        if ($user->first_login) {
            $user->first_login = false;
            $user->save();
        }

        return response()->api_success($response);
    }

    /**
     * @throws Exception
     */
    public function getUserPermission(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new Exception('не передан id пользователя');
        }

        if (!$user = User::query()->find($request->userId)) {
            throw new Exception('пользователь не найден');
        }

        return response()->json($user->getPermissionsViaRoles());
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function patchPermissionsRole(Request $request): JsonResponse
    {
        if (empty($request->roleId)) {
            throw new Exception('не передан id роли');
        }

        if (!$role = Role::query()->find($request->roleId)) {
            throw new Exception('роль не найдена');
        }

        if (!empty($request->permissions)) {
            foreach ($request->permissions as $name => $flag) {
                $permission = Permission::query()->where(['name' => $name])->get()->pop();
                if ($flag == 'true') {
                    //insertOrIgnore
                    RoleHasPermission::query()->insertOrIgnore([
                        'role_id' => $request->roleId,
                        'permission_id' => $permission->id
                    ]);
                    /* if(!RoleHasPermission::query()->where(['role_id' => $request->roleId, 'permission_id' => $permission->id])->get()){

                     }*/
                } else {
                    RoleHasPermission::query()->where([
                        'role_id' => $request->roleId, 'permission_id' => $permission->id
                    ])->delete();
                }
            }
        }

        return response()->json('изменены привязки прав');
    }


    /**
     * @throws Exception
     */
    public function deleteUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new Exception('не передан id пользователя');
        }

        if (!$user = User::query()->find($request->userId)) {
            throw new Exception('пользователь не найден');
        }

        (new UserService())->forgetAllCache();
        $user->delete();

        if ($user->trashed()) {
            // Пользователь помещен в корзину
            return response()->json('пользователь удален');
        }

        return response()->json('пользователь не был удален', 400);

    }

    /**
     * Подтверждение телефона у пользователя по коду
     */
    public function phoneConfirm(UserRepository $userRepository, PhoneService $phoneService, PhoneConfirmRequest $request)
    {
        $user = $userRepository->getByUnverifiedPhone($request->phone);
        $phoneService->setCurrentUserPhone($user);
        $user->save();
        return response()->api_success([]);
    }

    /**
     * Отправка нового кода человеку для подтверждения его телефона
     */
    public function phoneSendCode(UserRepository $userRepository, PhoneService $phoneService, PhoneSendCodeRequest $request)
    {
        $user = $userRepository->getByUnverifiedPhone($request->phone);
        $user->issueNewPhoneVerificationToken();
        $user->save();
        $phoneService->sendPhoneConfirmation($user);
        return response()->api_success([]);
    }
}
