<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Requests\Api\SettingsRequest;
use App\Http\Requests\Api\TransferAccountsRequest;
use App\Http\Requests\Api\UserAccountActivateRequest;
use App\Http\Requests\Api\UserCompanyActivateRequest;
use App\Models\Account;
use App\Models\Permission;
use App\Models\Platform;
use App\Models\Role;
use App\Models\TableModel;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Services\AccountServices;
use App\Services\ChangeRoles;
use App\Services\PermissionService;
use App\Services\UserCharacteristic;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    const  IS_SELECTED = 1;
    const  IS_NOT_SELECTED = 0;

    /** @var string Tmp must remove after test and find that queue has normally functionality */
    public $newAccountUrl = '/notifications/new-account/';

    /**
     * Получение всех аккаунтов пользователя
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        return response()->api_success($user->accounts()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($id, Request $request)
    {
        $request->validate([
            'platform_id' => 'required|integer',
            'platform_client_id' => 'required|min:1|max:250',
            'platform_api_key' => 'required|min:3|max:250',
        ]);
        if (isset($request->user['id']) && $request->user['id'] == $id) {
            $user = User::findOrFail($id);
            $account = $user->accounts()->create([
                'platform_id' => $request->get('platform_id'),
                'platform_client_id' => $request->get('platform_client_id'),
                'platform_api_key' => $request->get('platform_api_key'),
                'title' => $request->get('title'),
                'description' => $request->get('description'),
            ]);

            return response()->api_success($account);
        }

        return response()->api_fail('Аккаунт не сохранён');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $user_id, $id)
    {
        if ($request->user['id'] == $user_id) {
            $user = User::findOrFail($user_id);
            $account = $user->accounts()->where('accounts.id', $id)->first();
            if ($account) {
                return response()->api_success($account, 200);
            }
        }
        return response()->api_fail('Не найден аккаунт у пользователя');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $user_id, $id)
    {
        $request->validate([
            'platform_id' => 'required|integer',
            'platform_client_id' => 'required|min:3|max:250',
            'platform_api_key' => 'required|min:3|max:250',
        ]);
        if (isset($request->user['id']) && $request->user['id'] == $user_id) {
            $user = User::findOrFail($user_id);
            $user->accounts()->firstWhere('accounts.id', $id)->update([
                'platform_id' => $request->get('platform_id'),
                'platform_client_id' => $request->get('platform_client_id'),
                'platform_api_key' => $request->get('platform_api_key'),
                'title' => $request->get('title'),
                'description' => $request->get('description'),
            ]);
            // todo Get info from Roman add listener here or not
            return response()->api_success($user->accounts());
        }

        return response()->api_fail('Не удалось обновить аккаунт');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $user_id, $id)
    {
        if (isset($request->user['id']) && $request->user['id'] == $user_id) {
            $user = User::findOrFail($user_id);
            $user->accounts()->firstWhere('accounts.id', $id)->delete();
            // todo Get info from Roman add listener here or not
            return response()->api_success($user->accounts());
        }

        return response()->api_fail('Не удалось удалить аккаунт');
    }

    /**
     * Получить список аккаунтов пользователя по платформе
     *
     * @param  array  $platformId
     * @return JsonResponse
     */
    public function getAllUserAccounts($platformId = null)
    {
        $user = auth()->user();
        $availableAccounts = $user->accounts();

        if ($platformId > 0) {
            $availableAccounts->where('accounts.platform_id', '=', $platformId);
        }

        return response()->api_success($availableAccounts->get());
    }

    /**
     * Получить список всех аккаунтов по платформе
     *
     * @param  array  $platformId
     * @return JsonResponse
     */
    public function getAllAccounts($platformId = null)
    {
        if ($platformId > 0) {
            $availableAccounts = Account::where('accounts.platform_id', '=', $platformId)->get();
        } else {
            $availableAccounts = Account::all();
        }

        return response()->api_success($availableAccounts);
    }

    /**
     * Получение всех пользователей
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        $users = User::query()
            ->select([
                'users.id', 'users.name', 'users.email', 'users.phone', 'p.title', 'users.balance', 'users.created_at',
                'users.updated_at', 'ta.status', 't.description'
            ])
            ->leftJoin('user_account as ua', 'users.id', '=', 'ua.user_id')
            ->leftJoin('accounts as a', 'a.id', '=', 'ua.account_id')
            ->leftJoin('platforms as p', 'p.id', '=', 'a.platform_id')
            ->leftJoin('tariff_activity as ta', 'users.id', '=', 'ta.user_id')
            ->leftJoin('tariffs as t', 't.id', '=', 'ta.tariff_id')
            ->orderBy('users.id')->paginate(20);

        try {
            $availabilityVa = \DB::connection('va')->getPdo();
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
            $availabilityVa = false;
        }


        foreach ($users->items() as $k => &$user) {
            $users[$k]['roles'] = User::query()->select(['r.name'])->join('model_has_roles as hr', function ($q) {
                $q->on('hr.model_id', '=', 'users.id')->where(['hr.model_type' => 'App\Models\User']);
            })->join('roles as r', 'r.id', '=', 'hr.role_id')->where(['users.id' => $user['id']])->get()->toArray();

            if ($availabilityVa) {
                $users[$k]['op_cnt'] = \DB::connection('va')->table('oz_products as op')->where(['op.user_id' => $user['id']])->count();
                $users[$k]['wb_cnt'] = \DB::connection('va')->table('wb_products as wp')->where(['wp.user_id' => $user['id']])->count();
            } else {
                $users[$k]['op_cnt'] = $users[$k]['wb_cnt'] = ' Нет данных ';
            }
        }
        return response()->json($users);
    }

    /**
     * Получение платформ
     * todo в последствии нужно добавить поле is_active и показывать и отмечать в нем только активные / неактивные платформы
     * @return JsonResponse
     */
    public function getPlatforms()
    {
        return response()->json(Platform::whereIn('id', [1, 2, 3])->get());
    }

    /**
     * Получение всех пользователей с аккаунтами
     */
    public function getAllUsersAndAccounts()
    {
        return response()->json(User::with('accounts')->orderBy('id')->get());
    }

    /**
     * Получение аккаунта по id
     */
    public function getAccount($id)
    {
        return response()->json(Account::findOrFail($id));
    }

    /**
     * Получение всех активных аккаунтов adm
     */
    public function getAllAdmAccounts()
    {
        return response()->json(Account::getAllAdmAccounts());
    }

    /**
     * Получение всех активных аккаунтов ozon seller
     */
    public function getAllSellerOzonAccounts()
    {
        return response()->json(Account::getAllSellerOzonAccounts());
    }

    /**
     * Установка аккаунта пользователя по умолчанию
     *
     * @param  UserAccountActivateRequest  $request
     * @param  AccountServices  $accountServices
     * @return mixed
     */
    public function setDefaultAccount(UserAccountActivateRequest $request, AccountServices $accountServices)
    {
        $currentAccount = $accountServices->setDefaultAccount($request->get('id'));

        return response()->api_success($currentAccount);
    }

    /**
     * Установка компании пользователя по умолчанию.
     *
     * @param  UserCompanyActivateRequest  $request
     * @param  AccountServices  $accountServices
     * @return mixed
     */
    public function setDefaultCompany(UserCompanyActivateRequest $request, AccountServices $accountServices)
    {
        $currentCompany = $accountServices->setDefaultCompany($request->get('id'));

        return response()->api_success($currentCompany);
    }

    /**
     * @throws \Exception
     */
    public function changeActiveUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new \Exception('не передан id пользователя');
        }

        if (!$user = User::query()->find($request->userId)) {
            throw new \Exception('не найден пользователь');
        }

        $user->is_active = $request->is_active;

        if (!$user->save()) {
            throw new \Exception('Не удалось изменить активность пользователя');
        }

        (new UserService())->forgetUserDataCache();

        return response()->json('изменена активность пользователя');
    }

    /**
     * @throws \Exception
     */
    public function changePasswordUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new \Exception('не передан id пользователя');
        }

        $request->validate(['password' => 'required|confirmed']);

        if (!$user = User::query()->find($request->userId)) {
            throw new \Exception('Пользователь не найден');
        }

        $user->password = Hash::make($request->password);
        if (!$user->save()) {
            throw new \Exception('Не удалось сменить пароль пользователя');
        }

        (new UserService())->forgetUserDataCache();

        return response()->json('сменен пароль пользователя');
    }

    /**
     * @throws \Exception
     */
    public function editUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new \Exception('не передан id пользователя');
        }

        if (!empty($request->changeUserFields['email'])) {
            $request->validate(['email' => 'required|email|unique:users']);
        }

        if (!empty($request->changeUserFields['phone'])) {
            $request->validate(['phone' => 'numeric']);
        }

        if (!empty($request->changeUserFields['inn'])) {
            $request->validate(['inn' => 'numeric']);
        }

        if (!$user = User::query()->find($request->userId)) {
            throw new \Exception('Пользователь не найден');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->unverified_phone = $request->phone;
        $user->inn = $request->inn;

        if (!$user->save()) {
            throw new \Exception('Не удалось внести изменения пользователю');
        }

        (new UserService())->forgetAllCache();

        return response()->json('Внесены изменения о пользователе');
    }

    public function changePlatforms(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new \Exception('не передан id пользователя');
        }

        $obj = User::query()
            ->select(['p.id'])
            ->Join('user_account as ua', 'users.id', '=', 'ua.user_id', 'left')
            ->Join('accounts as a', 'a.id', '=', 'ua.account_id', 'left')
            ->join('platforms as p', 'p.id', '=', 'a.platform_id', 'left')
            ->where(['users.id' => $request->userId])
            ->get();


        if (!$obj) {
            foreach ($request->platforms as $platforms) {
                if (!$id = Platform::query()->find($platforms)->id) {
                    throw new \Exception('роль не найдена');
                }
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
                    throw new \Exception('роль не найдена');
                }

            }
        }

        $diff = array_diff($request->roles, $roles_user);
        if (!empty($diff)) {
            foreach ($diff as $role) {
                if (!$name = Role::query()->find($role)->name) {
                    throw new \Exception('роль не найдена');
                };
            }
        }


        return response()->json('изменены маркетплейсы');
    }

    /**
     * Получение всех аккаунтов по типу платформы
     * @param  Request  $request
     * @param $id
     * @return JsonResponse|void
     */
    public function getAllSellerAccounts(Request $request, $id)
    {
        $platform = Platform::find($id);
        if ($platform) {
            return response()->json($platform->accounts()->get());
        }
    }

    /**
     * Create access for user account (create account)
     * @param  SettingsRequest  $request
     * @return JsonResponse
     */
    public function setAccess(SettingsRequest $request)
    {
        try {
            $account = Account::saveAccount($request);

            return response()->api_success($account);
        } catch (\Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Edit access for user account (create account)
     * @param  SettingsRequest  $request
     * @return JsonResponse
     */
    public function editAccess(SettingsRequest $request)
    {
        try {
            $response = Account::saveAccount($request);
            return response()->api_success($response);
        } catch (\Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Изменение ролей пользователей
     * @param  Request  $request
     * @return JsonResponse
     */
    public function changeRolesUser(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            return response()->api_fail('не передан id пользователя', [], 422, 0);
        }

        (new ChangeRoles())->setUserId($request->userId)->setRoles($request->roles)->run();
        (new UserService())->forgetPermissionsCache();

        return response()->json('Изменены роли пользователя');
    }

    /**
     * Получить список моделей к которым можно привязать роли
     * @return JsonResponse
     */
    public function getModels(): JsonResponse
    {
        try {
            return response()->json(TableModel::query()->select(['id', 'model', 'name'])->get());
        } catch (\Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * Добавить роль
     * @param  RoleRequest  $request
     * @return JsonResponse
     */
    public function addRoles(RoleRequest $request): JsonResponse
    {
        $role = Role::query()->create([
            'name' => $request->input('role'),
            'description' => $request->input('description'),
            'guard_name' => 'admin',
            'table_model_id' => $request->input('id')
        ]);
        return response()->json($role);
    }

    /**
     * Получить список ролей
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        return response()->json(Role::all());
    }

    /**
     * Получить роль
     * @return JsonResponse
     */
    public function getRole($roleId): JsonResponse
    {
        return response()->json(Role::find($roleId));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getUserPermission(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            return response()->api_fail('не передан id пользователя', [], 400, 0);
        }

        if (!$user = User::query()->find($request->userId)) {
            return response()->api_fail('пользователь не найден', [], 400, 0);
        }

        return response()->json($user->getPermissionsViaRoles());
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getPermissionsRole(Request $request): JsonResponse
    {
        if (empty($request->roleId)) {
            throw new Exception('не передан id роли');
        }

        if (!$role = Role::query()->find($request->roleId)) {
            throw new Exception('роль не найдена');
        }

        return response()->json($role->rolePermissions);
    }

    /**
     * @return JsonResponse
     */
    public function getAllPermission(): JsonResponse
    {
        return response()->json(Permission::all());
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function patchPermissionsRole(Request $request): JsonResponse
    {
        if (empty($request->roleId)) {
            return response()->api_fail('не передан id роли', [], 400, 0);
        }

        if (!$role = Role::query()->find($request->roleId)) {
            return response()->api_fail('роль не найдена', [], 400, 0);
        }

        if (!empty($request->permissions)) {
            $permission = new PermissionService();
            $permission->bindPermissionsRole($request->roleId, $request->permissions);
        }

        return response()->json('изменены привязки прав');
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getUser(Request $request)
    {
        if (empty($request->userId)) {
            return response()->api_fail('не передан id пользователя', [], 400, 0);
        }
        $user = (new UserCharacteristic())->setUserId($request->userId)->get();
        return response()->json($user);
    }

    /**
     * @param TransferAccountsRequest $request
     * @param AccountServices $accountServices
     * @return JsonResponse
     */
    public function transferAccounts(TransferAccountsRequest $request, AccountServices $accountServices)
    {
        try {
            $user = \Auth::user();

            if ($request->account_id) {
                $result = $accountServices->transferAccount($request->account_id, $request->company_from_id, $request->company_to_id, $user->id);
            } else {
                $result = $accountServices->transferAccounts($request->company_from_id, $request->company_to_id, $user->id);
            }

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }

    /**
     * @param AccountRepository $accountRepository
     * @return JsonResponse
     */
    public function getAllAvailableUserAccounts(AccountRepository $accountRepository)
    {
        try {
            return response()->api_success($accountRepository->getAllAvailableUserAccounts(auth()->user()));
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
