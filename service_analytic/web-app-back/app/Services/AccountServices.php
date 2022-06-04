<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserCompany;
use App\Services\Account\CompanyHandler;
use App\Services\Account\UserHandler;
use Auth;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AccountServices
{

    /**
     * @param $id
     *
     * @return Account|false
     */
    public function setDefaultAccount($id): Account|false
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->getSelectedCompany()) {
            $this->setDefaultCompanyAccount($user->id, $id);
        } else {
            $this->setDefaultUserAccount($user->id, $id);
        }

        $newAccount = Account::find($id);
        (new UserService())->forgetAllCache();

        return $newAccount;
    }

    /**
     * @param User $user
     * @return void
     */
    public function setFirstAvailableAccountAsDefaultIfNotForUser(User $user)
    {
        if (!$user->getSelectedUserAccount()) {
            $firstAccount = $user->accounts()->first();
            if ($firstAccount) {
                (new AccountServices())->setDefaultUserAccount($user->id, $firstAccount->id);
            }
        }
    }

    /**
     * @param User $user
     * @param Company $company
     * @param int|null $selectedAccountId
     * @return void
     */
    public function setFirstAvailableAccountAsDefaultIfNotForCompany(User $user, Company $company, int|null $selectedAccountId)
    {
        if (!$selectedAccountId) {
            $firstAccount = $company->accounts()->first();
            if ($firstAccount) {
                (new AccountServices())->setDefaultCompanyAccount($user->id, $firstAccount->id);
            }
        }
    }

    /**
     * @param int $userId
     * @param int $accountId
     * @return void
     */
    public function setDefaultUserAccount(int $userId, int $accountId)
    {
        UserAccount::where('user_id', $userId)->update(['is_selected' => 0]);

        UserAccount::where('user_id', $userId)->where('account_id', $accountId)->update(['is_selected' => 1]);
    }

    /**
     * @param int $userId
     * @param int $accountId
     * @return void
     */
    public function setDefaultCompanyAccount(int $userId, int $accountId)
    {
        UserCompany::where('user_id', $userId)->update(['selected_account_id' => null]);

        UserCompany::where('user_id', $userId)
            ->where('is_selected', 1)
            ->update(['selected_account_id' => $accountId]);
    }

    /**
     * @param $id
     *
     * @return Company|null|false
     */
    public function setDefaultCompany($id): Company|null|false
    {
        $userId = Auth::user()->id ?? null;

        if (!$userId) {
            return false;
        }

        UserCompany::where('user_id', $userId)->update(['is_selected' => 0]);
        UserCompany::where('user_id', $userId)->where('company_id', $id)->update(['is_selected' => 1]);
        $newAccount = Company::where('id', $id)->first();
        (new UserService())->forgetAllCache();

        return $newAccount;
    }

    /**
     * @param int $platformId
     * @param int $accountId
     * @return bool
     * @throws \Exception
     */
    public function deleteFromTrackingAccountProducts(int $platformId, int $accountId): bool
    {
        if ($platformId === 1) {
            $platform = 'ozon';
        } elseif($platformId === 3) {
            $platform = 'wildberries';
        } else {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization-Web-App' => config('virtual-assistant.token'),
        ])->post(config('virtual-assistant.url_v2') . '/company-model/' . $platform . '/delete-products-for-all-account-users', [
            'account_id' => $accountId
        ]);

        if ($response->status() >= 300 || $response->status() < 200) {
            throw new \Exception('Unable to delete account products');
        }

        return true;
    }

    /**
     * @param int $accountId
     * @param int $companyFromId
     * @param int $companyToId
     * @param int $userId
     * @return mixed
     * @throws \Exception
     */
    public function transferAccount(int $accountId, int $companyFromId, int $companyToId, int $userId): mixed
    {
        $account = Account::where('id', $accountId)->first();

        $platformId = $account->platform_id;

        if ($companyFromId === 0) {
            $userAccountFromHandler = new UserHandler($account, $userId);
        } else {
            $userAccountFromHandler = new CompanyHandler($account, $companyFromId);
        }

        if ($companyToId === 0) {
            $userAccountToHandler = new UserHandler($account, $userId);
        } else {
            $userAccountToHandler = new CompanyHandler($account, $companyToId);
        }

        return ModelHelper::transaction(function () use ($userAccountFromHandler, $userAccountToHandler, $platformId, $accountId) {
            $this->deleteFromTrackingAccountProducts($platformId, $accountId);

            $userAccountToHandler->attachAccount();

            $userAccountFromHandler->detachAccount();

            return true;
        });
    }

    /**
     * @param int $companyFromId
     * @param int $companyToId
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function transferAccounts(int $companyFromId, int $companyToId, int $userId): bool
    {
        $user = User::where('id', $userId)->first();

        if ($companyFromId === 0) {
            $accounts = $user->accounts;
        } else {
            $accounts = Company::where('id', $companyFromId)->first()->accounts;
        }

        foreach ($accounts as $account) {
            $this->transferAccount($account->id, $companyFromId, $companyToId, $userId);
        }

        return true;
    }

    /**
     * Set first available user account as selected by default
     * for each provided user
     *
     * @param  Collection|array|int|string  $userIds  Array or simple collection of user ids
     *                                     or eloquent collection of User models
     *
     * @param  bool  $refresh  Set all other user accounts as not selected
     *
     * @return int number of updated rows
     */
    public static function setFirstAvailableAccountAsSelectedForUsers(Collection|array|int|string $userIds, $refresh = false): int
    {
        if (!is_iterable($userIds)) {
            if (!is_numeric($userIds)) {
                return 0;
            }
            $userIds = [$userIds];
        }

        if ($userIds instanceof EloquentCollection) {
            $userIds = $userIds->pluck('id');
        }

        if (empty($userIds) || $userIds instanceof Collection && $userIds->isEmpty()) {
            return 0;
        }

        if ($refresh) {
            UserAccount::query()->whereIn('user_id', $userIds)->update(['is_selected' => false]);
        }

        $result = Account::query()
            ->select('user_id', \DB::raw('min(accounts.id) as accountId'))
            ->whereIn('user_id', $userIds)
            ->join('user_account', 'account_id', 'accounts.id')
            ->groupBy('user_id')
            ->pluck('accountId', 'user_id');

        if ($result->isEmpty()) {
            return 0;
        }

        return UserAccount::query()
            ->where(function ($query) use ($result) {
                foreach ($result as $userId => $accountId) {
                    $query->orWhere(fn($query) => $query->where(['account_id' => $accountId, 'user_id' => $userId]));
                }
            })
            ->update(['is_selected' => true]);
    }
}
