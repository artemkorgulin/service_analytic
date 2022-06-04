<?php

namespace App\Services;

use App\Contracts\RoleTypeModelInterface;
use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Добавление и удаление привязок пользователей к моделям ролей
 */
class ChangeRoles
{
    /**
     * Id пользователя
     * @var int
     */
    protected int $userId;

    /**
     * Модель пользователей
     * @var ?User
     */
    protected ?User $user;

    /**
     * Модель аккаунта
     * @var ?Account
     */
    protected ?Account $account;

    /**
     * Массив передаваемых ролей привязанных к пользователю или его аккаунту
     * @var array
     */
    protected array $roles;

    /**
     * Пул ролей
     * @var array
     */
    protected array $roles_user = [];

    public static function vrd($model)
    {
        $query = str_replace(array('?'), array('\'%s\''), $model->toSql());
        $query = vsprintf($query, $model->getBindings());
        Log::warning($query);
    }

    /**
     * Id пользователя
     * @param  int  $userId
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param  array  $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * точка запуска
     */
    public function run()
    {
        $this->getUser()->bind();
    }

    /**
     * Вносим изменения в модель ролей
     */
    private function bind()
    {
        if (!$obj = $this->getModelHasRole()) {
            $this->addNewRole();
        }
        foreach ($obj as $item) {
            $this->roles_user[] = $item['role_id'];
        }
        $this->changeRole($this->roles_user);
    }

    /**
     * Найти если существует запись привязки одной из моделей
     * @return array
     */
    private function getModelHasRole(): array
    {
        $accounts = $this->user->accounts()->get()->pluck('id')->toArray();
        $role = Role::query()->select(['roles.id as role_id'])
            ->join('model_has_roles as hr', 'hr.role_id', '=', 'roles.id')
            ->groupBy('id');

        if (!empty($accounts)) {
            $role->where(function ($sub) use ($accounts) {
                $sub->whereIn('hr.model_id', $accounts)->where('hr.model_type', '=', 'App\\Models\\Account');
            })->orWhere(function ($sub) {
                $sub->where('hr.model_id', '=', $this->userId)->where('hr.model_type', '=', 'App\\Models\\User');
            });
        } else {
            $role->where('hr.model_type', '=', 'App\\Models\\User');
        }

        return $role->get()->toArray();
    }

    /**
     * Добавить новую роль
     * @return JsonResponse
     */
    protected function addNewRole(): JsonResponse
    {
        foreach ($this->roles as $role) {
            if (!$model = Role::query()->find($role)) {
                return response()->api_fail('роль не найдена', [], 422, 0);
            }
            $this->assign($model);
        }
        return response()->json('Добавлены новые роли');
    }

    /**
     * Привязать роль
     * @param $model
     */
    private function assign($model)
    {
        if ($model->table_model_id == RoleTypeModelInterface::TYPE_USER) {
            $this->user->assignRole($model->name);
        }
        if ($model->table_model_id == RoleTypeModelInterface::TYPE_USER_ACCOUNT) {
            $accounts = $this->getAccounts();
            foreach ($accounts as $account) {
                $account->assignRole($model->name);
            }
        }
    }

    /**
     * Получить модель аккаунтов
     * @return
     */
    public function getAccounts()
    {
        return $this->user->accounts;
    }

    /**
     * Изменить роль
     * @param  array  $roles_user
     * @return void
     */
    protected function changeRole(array $roles_user)
    {
        $diff = array_diff($roles_user, $this->roles);
        if (!empty($diff)) {
            foreach ($diff as $role) {
                if (!$model = Role::query()->find($role)) {
                    return response()->api_fail('Роль не найдена', [], 400, 0);
                }
                $this->remove($model);
            }
        }

        $diff = array_diff($this->roles, $roles_user);
        if (!empty($diff)) {
            foreach ($diff as $role) {
                if (!$model = Role::query()->find($role)) {
                    return response()->api_fail('Роль не найдена', [], 400, 0);
                }
                $this->assign($model);
            }
        }
    }

    /**
     * Отвязать роль
     * @param $model
     */
    private function remove($model)
    {
        if ($model->table_model_id == RoleTypeModelInterface::TYPE_USER) {
            $this->user->removeRole($model->name);
        }
        if ($model->table_model_id == RoleTypeModelInterface::TYPE_USER_ACCOUNT) {
            $accounts = $this->getAccounts();
            foreach ($accounts as $account) {
                $account->removeRole($model->name);
            }
        }
    }

    /**
     * Получить модель пользователя
     * @return $this
     */
    private function getUser(): self
    {
        if (!$this->user = User::query()->find($this->userId)) {
            return response()->api_fail('не найден пользователь', [], 422, 0);
        }
        return $this;
    }

}
