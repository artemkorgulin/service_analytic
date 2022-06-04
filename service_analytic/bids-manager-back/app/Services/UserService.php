<?php

namespace App\Services;

use App\Constants\Platform;
use App\DataTransferObjects\AccountDTO;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{

    /**
     * Check user permission
     *
     * @param  string  $permission
     *
     * @return bool
     */
    public static function can(string $permission): bool
    {
        $permissionList = request()->permissions;

        if (empty($permissionList) || !is_array($permissionList)) {
            return false;
        }

        return in_array($permission, $permissionList);
    }


    /**
     * Получение аккаунта пользователя по его id
     *
     * @param  numeric  $id
     *
     * @return AccountDTO|null
     */
    public static function getWabAccount($id): ?AccountDTO
    {
        $accountData = DatabaseService::getWabTableQuery('accounts')->find($id);
        if (!empty($accountData)) {
            return new AccountDTO((array) $accountData);
        }

        return null;
    }


    /**
     * Получение первого VA аккаунта пользователя
     *
     * @return stdClass
     */
    public static function getVaAccount($userId = null)
    {
        if ($userId === null) {
            $userId = self::getUserId();
        }

        $vaAccount = DatabaseService::getWabTableQuery('accounts')
            ->leftJoin('user_account AS ua', 'accounts.id', '=', 'ua.account_id')
            ->where(['ua.user_id' => $userId, 'accounts.platform_id' => Platform::SELLER_OZON_PLATFORM_ID])
            ->first();

        if (empty($vaAccount)) {
            throw new Exception('Не найден VA аккаунт для работы с рекламными кампаниями');
        }

        return $vaAccount;
    }


    /**
     * Get all accounts from current user
     *
     * @return array
     */
    public static function getAllAccounts(): array
    {
        $accounts = [];
        if (isset(request()->accounts) && count(request()->accounts) > 0) {
            foreach (request()->accounts as $account) {
                if ($account['platform_id'] !== Platform::ADM_OZON_PLATFORM_ID) {
                    continue;
                }
                $accounts[] = $account;
            }
        }

        return $accounts;
    }


    /**
     * Get all adm accounts in wab DB
     *
     * @param  array  $fields
     * @param  iterable  $accountIds
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAdmAccounts(array $fields = [], iterable $accountIds = []): Collection
    {
        $accountsData = self::getAdmAccountsQuery($fields)
            ->when(!empty($accountIds), function (Builder $query) use ($accountIds) {
                $query->whereIn('id', $accountIds);
            })
            ->get();

        $accounts = new Collection();
        foreach ($accountsData as $accountData) {
            $accounts->put($accountData->id, new AccountDTO((array) $accountData));
        }

        return $accounts;
    }


    /**
     * Get account by id
     *
     * @param  int  $id
     *
     * @return \App\DataTransferObjects\AccountDTO|null
     */
    public static function findAdmAccount(int $id): ?AccountDTO
    {
        $accountData = (array) self::getAdmAccountsQuery()->find($id);
        if (!$accountData) {
            return null;
        }

        return new AccountDTO($accountData);
    }


    /**
     * @param  callable  $callback
     * @param  int  $chunk
     * @param  array  $fields
     *
     * @return bool
     */
    public static function chunkAllAdmAccountsGet(callable $callback, int $chunk = 100, array $fields = []): bool
    {
        return self::getAdmAccountsQuery($fields)->orderBy('id')->chunk($chunk, $callback);
    }


    /**
     * @param  mixed  $fields
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private static function getAdmAccountsQuery(mixed $fields = []): Builder
    {
        $query = DatabaseService::getWabTableQuery('accounts');

        if (!empty($fields)) {
            $query->select($fields);
        }

        $query
            ->where('is_active', true)
            ->where('platform_id', '=', Platform::ADM_OZON_PLATFORM_ID);

        return $query;
    }


    /**
     * Получение данных по текущему аккаунту
     *
     * @return array
     */
    public static function getCurrentAccount(): ?array
    {
        $accounts = request()->accounts;
        if (!request()->accounts) {
            abort('401');
        }
        foreach ($accounts as $account) {
            if ($account['pivot']['is_selected']) {
                return $account;
            }
        }

        return null;
    }


    /**
     * Get current account from current user
     *
     * @return int|null
     */
    public static function getCurrentAccountId(): ?int
    {
        $accounts = self::getAllAccounts();
        if ($accounts > 0) {
            foreach ($accounts as $account) {
                if ($account['pivot']['is_selected'] == 1) {
                    return $account['id'];
                }
            }
        }

        return null;
    }


    /**
     * Get current user data
     *
     * @return array
     */
    public static function getUser(): array
    {
        return request()->user ?? [];
    }


    /**
     * Get current user id
     *
     * @return int
     */
    public static function getUserId(): ?int
    {
        if (empty(request()->get('user')) || empty(request()->get('user')['id'])) {
            return null;
        }

        return (int) request()->get('user')['id'];
    }
}
