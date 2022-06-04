<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Billing\UserTariffRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserCompanyRepository;
use Auth;
use AnalyticPlatform\LaravelHelpers\Constants\DateTimeConstants;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class UserService
 * @package App\Services
 * @method static forgetAccountsCacheForUsers(iterable|User $users)
 * @method static forgetCompaniesCacheForUsers(iterable|User $users)
 * @method static forgetPermissionsCacheForUsers(iterable|User $users)
 * @method static forgetTariffCacheForUsers(iterable|User $users)
 * @method static forgetUserDataCacheForUsers(iterable|User $users)
 */
class UserService
{

    public const USER_CACHE_TIME = DateTimeConstants::COUNT_SECONDS_IN_WEEK;

    private User $user;


    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
    }


    /**
     * Check user permission
     *
     * @param string $permission
     *
     * @return bool
     */
    public function can(string $permission): bool
    {
        return $this->user->getPermissionsViaRoles()->contains('name', $permission);
    }

    public function forgetAccountsCache(): void
    {
        Cache::forget($this->getAccountsCacheKey('proxy'));
        Cache::forget($this->getAccountsCacheKey('me'));
    }

    /**
     * @return void
     */
    public function forgetAccountCache(): void
    {
        Cache::forget($this->getAccountCacheKey());
    }

    public function forgetCompaniesCache(): void
    {
        Cache::forget($this->getCompaniesCacheKey());
    }

    public function forgetAllCache(): void
    {
        $this->forgetAccountsCache();
        $this->forgetPermissionsCache();
        $this->forgetTariffCache();
        $this->forgetUserDataCache();
        $this->forgetCompaniesCache();
        $this->forgetAccountCache();
    }


    public function forgetPermissionsCache(): void
    {
        Cache::forget($this->getPermissionsCacheKey());
    }


    public function forgetTariffCache(): void
    {
        Cache::forget($this->getTariffCacheKey());
    }


    public function forgetUserDataCache(): void
    {
        Cache::forget($this->getUserDataCacheKey());
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function setAccountsCache(string $type)
    {
        return Cache::remember($this->getAccountsCacheKey($type), self::USER_CACHE_TIME, function () use ($type) {
            return $this->getCurrentAccounts($type);
        });
    }

    /**
     * @return mixed
     */
    public function setAccountCache()
    {
        return Cache::remember($this->getAccountCacheKey(), self::USER_CACHE_TIME, function () {
            return $this->getCurrentAccount();
        });
    }

    /**
     * @param string $type
     * @return array
     */
    public function getCurrentAccounts(string $type): array
    {
        $accountServsices = new AccountServices();
        $selectedCompany = $this->user->getSelectedCompany();
        if ($selectedCompany) {
            $accountServsices->setFirstAvailableAccountAsDefaultIfNotForCompany($this->user, $selectedCompany, $selectedCompany->pivot->selected_account_id);

            //Обновляем информация о выбранной компании($selectedCompany->fresh() не подходит, тк не обновляет pivot).
            $selectedCompany = $this->user->getSelectedCompany();

            $accounts = $this->getAccounts($selectedCompany->accounts(), $type);
            foreach ($accounts as $key => $account) {
                $isSelected = 0;
                if ($selectedCompany->pivot->selected_account_id === $account['id']) {
                    $isSelected = 1;
                }

                $accounts[$key]['pivot']['is_selected'] = $isSelected;
            }
        } else {
            $accountServsices->setFirstAvailableAccountAsDefaultIfNotForUser($this->user);

            $accounts = $this->getAccounts($this->user->accounts(), $type);
        }

        return $accounts;
    }

    /**
     * @return array|null
     */
    public function getCurrentAccount(): array|null
    {
        return $this->user->getSelectedUserAccountDependingContext()?->toArray();
    }

    /**
     * @param $accounts
     * @param string $type
     * @return array
     */
    private function getAccounts($accounts, string $type): array
    {
        $result = $accounts->with(['platform']);

        if ($type === 'proxy') {
            $result = $result->get([
                'accounts.id',
                'accounts.title',
                'accounts.platform_id',
                'accounts.platform_client_id',
                'accounts.platform_api_key',
                'accounts.params'
            ])->each(function ($account) {
                /**
                 * Bugfix. В итоговый массив всегда передаются данные по платформе. Режем ненужные поля в связи с этим.
                 * Даже без with связь всё равно в итоговый массив приходит.
                 */
                $account->platform->makeHidden(['description']);
            })->toArray();
        } elseif ($type === 'me') {
            $result = $result->get()->keyBy('id')->toArray();
        }

        return $result;
    }

    public function setCompaniesCache()
    {
        return Cache::remember($this->getCompaniesCacheKey(), self::USER_CACHE_TIME, function () {
            return (new CompanyRepository())->list(\Auth::user());
        });
    }

    public function setPermissionsCache()
    {
        return Cache::remember($this->getPermissionsCacheKey(), self::USER_CACHE_TIME, function () {
            $permissions = $this->user->getPermissionsViaRoles()->pluck('name', 'id')->toArray();

            if ($selectedCompany = $this->user->getSelectedCompany()) {
                $permissions = array_merge($permissions, (new UserCompanyRepository())->getPermissions($this->user, $selectedCompany->id));
            }

            return $permissions;
        });
    }

    public function setTariffCache()
    {
        return Cache::remember($this->getTariffCacheKey(), self::USER_CACHE_TIME, function () {
            return (new UserTariffRepository())->getTariffDependingContext($this->user);
        });
    }

    public function setUserDataCache()
    {
        return Cache::remember($this->getUserDataCacheKey(), self::USER_CACHE_TIME, function () {
            return $this->user->getFilteredProxyAttributes();
        });
    }


    /**
     * @param User $user
     *
     * @return static
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }


    /**
     * @param string $type
     * @return string
     */
    public function getAccountsCacheKey(string $type): string
    {
        return $type . '_user_accounts_' . $this->user->id;
    }

    /**
     * @return string
     */
    public function getAccountCacheKey(): string
    {
        return 'proxy_user_account_' . $this->user->id;
    }

    /**
     * @return string
     */
    public function getCompaniesCacheKey(): string
    {
        return 'me_user_companies_' . $this->user->id;
    }


    /**
     * @return string
     */
    public function getPermissionsCacheKey(): string
    {
        return 'proxy_user_permissions_' . $this->user->id;
    }


    /**
     * @return string
     */
    public function getTariffCacheKey(): string
    {
        return 'proxy_user_tariffs_' . $this->user->id;
    }


    /**
     * @return string
     */
    public function getUserDataCacheKey(): string
    {
        return 'proxy_user_' . $this->user->id;
    }


    /**
     * Magic method that adds ability
     * to call all "forget cache" methods statically
     *
     * @param  string  $name
     * @param  array  $arguments
     *
     * @return void
     * @throws \Exception
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        if (!preg_match('/^(forget(Accounts|Companies|Permissions|Tariff|UserData)Cache)ForUsers$/', $name, $matches)) {
            throw new \Exception(sprintf('Method %s not found', $name));
        }

        /** @var iterable|User $users */
        $users = array_shift($arguments);
        if (!is_iterable($users)) {
            $users = [$users];
        }

        if (!empty($users) || $users instanceof Collection && $users->isNotEmpty()) {
            //get name of non-static method that'll be called
            //f.e. forgetAccountsCache for static method forgetAccountsCacheForUsers
            $method = $matches[1];

            $userService = new static(new User);

            foreach ($users as $user) {
                $userService->setUser($user)->{$method}(...$arguments);
            }
        }
    }
}
