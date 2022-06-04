<?php


namespace App\Services;


use App\Models\Role;
use App\Models\TariffActivity;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Характеристики пользователя
 * Class UserCharacteristic
 * @package App\Services
 */
class UserCharacteristic
{
    protected int $userId;

    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId): UserCharacteristic
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Получить характеристики пользователя
     * @return array
     */
    public function get(): array
    {
        $user = $this->getUser();
        $user['roles'] = $this->getRoles();
        $user['platforms'] = $this->getPlatforms();
        $user['tariff'] = $this->getTariff();
        return $user;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        if (!$user = User::query()->find($this->userId)->toArray()) {
            return response()->api_fail('Не найден пользователь', [], 422, 0);
        }

        return $user;
    }

    /**
     * Получить роли пользователя
     * @return array
     */
    protected function getRoles(): array
    {
        $accounts = $this->getAccounts();
        $roles = Role::query()->select(['roles.name', 'roles.id'])
            ->join('model_has_roles as hr', 'hr.role_id', '=', 'roles.id')
            ->groupBy('id');

        if (!empty($accounts)) {
            $roles->where(function ($sub) use ($accounts) {
                $sub->whereIn('hr.model_id', $accounts)->where('hr.model_type', '=', 'App\\Models\\Account');
            })->orWhere(function ($sub) {
                $sub->where('hr.model_id', '=', $this->userId)->where('hr.model_type', '=', 'App\\Models\\User');
            });
        } else {
            $roles->where('hr.model_type', '=', 'App\\Models\\User')
                ->where('hr.model_id', '=', $this->userId);
        }

        $sql = $roles->toSql();

        return $roles->get()->toArray();
    }

    /**
     * @return array
     */
    protected function getAccounts(): array
    {
        if ($accounts = array_column(UserAccount::query()->where(['user_id' => $this->userId])->get()->toArray(),
            'account_id')) {
            return $accounts;
        }
        return [];
    }

    /**
     * Получить аккаунт маркетплейса пользователя
     * @return array
     */
    protected function getPlatforms(): array
    {
        return User::query()->select(['p.id', 'p.title'])->leftJoin('user_account as ua', 'users.id', '=', 'ua.user_id')
            ->Join('accounts as a', 'a.id', '=', 'ua.account_id', 'left')
            ->join('platforms as p', 'p.id', '=', 'a.platform_id', 'left')
            ->where(['users.id' => $this->userId])->get()->toArray();
    }

    /**
     * Получить тариф пользователя
     * @return Builder|Model|object|null
     */
    protected function getTariff()
    {
        return User::query()
            ->select(['t.id', 't.description'])
            ->leftJoin('tariff_activity as ta', 'users.id', '=', 'ta.user_id')
            ->leftJoin('tariffs as t', 't.id', '=', 'ta.tariff_id')
            ->where(['users.id' => $this->userId, 'status' => TariffActivity::ACTVE])
            ->first();
    }

}
