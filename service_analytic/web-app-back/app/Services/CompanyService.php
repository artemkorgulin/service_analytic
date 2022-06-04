<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;

class CompanyService
{
    /**
     * @param Company $company
     * @param array $attributes
     * @return Company
     */
    public function updateCompany(Company $company, array $attributes): Company
    {
        $company->update($attributes);

        $this->clearCompanyUsersCache($company);

        return $company->unsetRelation('usersWithTrashed');
    }

    /**
     * @param Company $company
     * @return Company
     */
    public function deleteCompany(Company $company): Company
    {
        $company->delete();

        $this->clearCompanyUsersCache($company);

        return $company;
    }

    /**
     * @param  Company  $company
     * @return void
     */
    public function clearCompanyUsersCache(Company $company): void
    {
        $users = $company->usersWithTrashed;

        foreach ($users as $user) {
            (new UserService($user))->forgetAllCache();
        }
    }

    /**
     * Создать компанию для текущего пользователя
     *
     * @param array $data
     * @return Company
     */
    public function findOrCreateCompany(array $data): Company
    {
        $company = Company::firstOrCreate(
            [
                'inn' => $data['inn'],
                'kpp' => $data['kpp']
            ],
            [
                'name' => $data['name'] ?? null,
                'address' => $data['address'] ?? null
            ]
        );

        return $company;
    }

    /**
     * @param  array  $data
     * @return Company
     */
    public function createCompany(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Назначить пользователя администратором компании, если администраторов у неё нет
     */
    public function adoptCompany(Company $company, User $user)
    {
        if (!$this->haveAdmin($company)) {
            $user->company()->attach($company->id);
            $userCompany = UserCompany::where('user_id', $user->id)->where('company_id', $company->id)->first();
            $userCompany->assignRole('company.owner');
            $userCompany->assignRole('company.manager');

            (new UserService($user))->forgetAllCache();
        }
    }

    /**
     * Проверить - есть ли у компании привязанный человек
     */
    public function haveAdmin(Company $company)
    {
        $userCompanies = UserCompany::where('company_id', $company->id)->get();

        foreach ($userCompanies as $userCompany) {
            if ($userCompany->hasRole('company.owner')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Company $company
     * @param User $user
     * @param array $roles
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function userSetRole(Company $company, User $user, array $roles)
    {
        $userCompany = $user->userCompany($company->id)->first();
        $userCompany->assignRole($roles);

        (new UserService($user))->forgetAllCache();

        return $userCompany;
    }

    /**
     * @param Company $company
     * @param User $user
     * @param array $roles
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function userDeleteRole(Company $company, User $user, array $roles)
    {
        $userCompany = $user->userCompany($company->id)->first();
        foreach ($roles as $role) {
            $userCompany->removeRole($role);
        }

        (new UserService($user))->forgetAllCache();

        return $userCompany;
    }

    /**
     * @param int $userId
     * @param int $companyId
     * @return UserCompany
     */
    public function userCompanyAdd(int $userId, int $companyId): UserCompany
    {
        $userCompany = UserCompany::where('user_id', $userId)->where('company_id', $companyId)->first();
        if (!$userCompany) {
            $userCompany = UserCompany::withTrashed()
                ->updateOrCreate([
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ], ['deleted_at' => null]);

            $user = User::where('id', $userId)->first();

            (new UserService($user))->forgetAllCache();

            $company = Company::where('id', $companyId)->first();
            UsersNotification::dispatch(
                'company.add_user',
                [['id' => $user->id, 'lang' => 'ru', 'email' => $user->email]],
                ['company' => $company->name]
            );
        }

        return $userCompany;
    }

    /**
     * @param int $userId
     * @param int $companyId
     * @return int
     */
    public function userCompanyDelete(int $userId, int $companyId): int
    {
        $userCompany = UserCompany::where('company_id', $companyId)->where('user_id', $userId)->delete();

        $user = User::where('id', $userId)->first();

        (new UserService($user))->forgetAllCache();

        return $userCompany;
    }

    /**
     * Activate or deactivate company users.
     *
     * @param  bool  $activate
     * @param  Company|int  $company
     * @param  User|null  $owner
     * @return void
     */
    public function switchCompanyUsers(bool $isActive, Company|int $company, User|null $owner = null): void
    {
        if (is_int($company)) {
            $company = Company::find($company);
        }

        if (!$owner) {
            $owner = $company->getOwner();
        }
        $ownerId = $owner?->id ?? 0;

        UserCompany::where('company_id', $company->id)->where('user_id', '!=', $ownerId)->update([
            'is_active' => $isActive,
        ]);

        $this->clearCompanyUsersCache($company);
    }

    /**
     * @param  string  $email
     * @return User|null
     */
    public function searchUser(string $email): User|null
    {
        return User::select('id', 'name', 'email')->where('email', $email)->first();
    }
}
