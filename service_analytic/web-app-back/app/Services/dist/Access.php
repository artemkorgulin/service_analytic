<?php

namespace App\Services\dist;

use App\Http\Requests\Api\SettingsRequest;
use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use App\Models\UserAccount;
use App\Services\AccountServices;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


/**
 *
 */
abstract class Access
{
    /** @var Authenticatable|null */
    protected $user;

    /**
     * @var SettingsRequest
     */
    protected SettingsRequest $request;

    /**
     * Привязка к аккаунту
     * @TODO Нужно актуализировать код, много не нужного.
     * @throws \Throwable
     */
    protected function save()
    {
        /** @var User $user */
        $user = $this->user ?: Auth::user();

        (new UserService($user))->forgetAccountsCache();

        return ModelHelper::transaction(function () use ($user) {
            /** @var Account $account */
            if (!empty($this->request->account_id)) {
                $account = Account::find($this->request->account_id);
            } else {
                $account = $this->getAccount(withTrashed: true);
                if ($account && $account->trashed()) {
                    $account->restore();
                }
            }

            if (!empty($account)) {
                $this->updateExistingAccount($account);
            }

            /** @var Account $account */
            if ($account = $this->getAccount()) {
                $accountIsBind = $account->users()->where('user_id', $user->id)->exists();

                if ($accountIsBind) {
                    return $account;
                }

                if ($this->request->company_id) {
                    $this->attachCompany($account->id, $this->request->company_id);
                } else {
                    $user->accounts()->attach($account->id);
                    (new AccountServices())->setDefaultAccount($account->id);
                    static::setRole($user);
                }
            } else {
                $account = (new Account())->fill([
                    'platform_id' => $this->request->platform_id,
                    'platform_client_id' => $this->request->client_id ?? null,
                    'platform_api_key' => $this->request->client_api_key,
                    'title' => $this->request->title,
                    'description' => static::DESCRIBE,
                    'max_count_request_per_day' => Account::getMaxCountRequestPerDayForPlatform($this->request->platform_id)
                ]);
                $account->save();

                if ($this->request->company_id) {
                    $this->attachCompany($account->id, $this->request->company_id);
                } else {
                    $user->accounts()->attach($account->id);
                    $userAccount = UserAccount::where([
                        ['user_id', '=', $user->id], ['account_id', '=', $account->id]
                    ])->first();
                    $userAccount->is_account_admin = 1;
                    $userAccount->save();
                    (new AccountServices())->setDefaultAccount($account->id);
                    $user->assignRole('super.supplier');
                }
            }

            return $account;
        });
    }

    /**
     * @param int $accountId
     * @param int $companyId
     *
     * @return void
     */
    protected function attachCompany(int $accountId, int $companyId): void
    {
        $company = Company::find($companyId);

        $company->accounts()->attach($accountId);
    }


    /**
     * Get account by platform_id and client_id
     *
     * @param  bool  $withTrashed
     *
     * @return Builder|Model|object|null
     */
    public function getAccount(bool $withTrashed = false)
    {
        /** @var Builder $query */
        $query = Account::query()
            ->where([
                'platform_id'        => $this->request->platform_id,
                'platform_client_id' => $this->request->client_id
            ]);

        if ($withTrashed) {
            $query->withTrashed()->orderBy('deleted_at');
        }

        return $query->first();
    }


    /**
     * Set user to attach account
     *
     * @param  Authenticatable  $user
     *
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }


    /**
     * Update account's title and api key
     *
     * @param  Account  $account
     *
     * @return bool
     * @throws \Exception
     */
    protected function updateExistingAccount(Account $account): bool
    {
        $fillArray = ['title' => $this->request->title];
        if ($account->platform_client_id != $this->request->client_id) {
            //todo: replace with more specific exception class
            throw new \Exception('Изменение Client Id невозможно');
        }
        if ($account->platform_api_key != $this->request->client_api_key) {
            $fillArray = array_merge($fillArray, ['platform_api_key' => $this->request->client_api_key]);
        }

        return !empty(Account::where(['id' => $account->id])->update($fillArray));
    }
}
