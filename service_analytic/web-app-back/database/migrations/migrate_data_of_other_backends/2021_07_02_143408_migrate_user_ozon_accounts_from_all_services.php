<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Class MigrateUserOzonAccountsFromAllServices
 *
 * Миграция и объединение аккаунтов со всех серверов и баз на web-app
 */
class MigrateUserOzonAccountsFromAllServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Перенос аккаунтов с сервера ADM
        $accountsAdm = DB::connection('adm')->table('accounts')->get();
        $ozonPlatformId = DB::table('platforms')->where('title', '=', 'Ozon')->first()->id;
        $ozonAdmPlatformId = DB::table('platforms')->where('title', '=', 'Ozon Performance')->first()->id;
        $usersAccountsData = [];

        foreach ($accountsAdm as $account) {
            $usersAccountsData[] = $this->insertAccountRow([
                'platformId' => $ozonPlatformId,
                'clientId' => $account->seller_client_id,
                'apiKey' => $account->seller_api_key,
                'title' => $account->account_name.' Ozon',
                'isSelected' => $account->activity,
                'oldAccountId' => $account->id,
                'userId' => $account->user_id,
                'isAccountAdmin' => 1
            ]);
            $usersAccountsData[] = $this->insertAccountRow([
                'platformId' => $ozonAdmPlatformId,
                'clientId' => $account->perfomance_client_id,
                'apiKey' => $account->perfomance_client_secret,
                'title' => $account->account_name.' Ozon Performance',
                'isSelected' => $account->activity,
                'oldAccountId' => $account->id,
                'userId' => $account->user_id,
                'isAccountAdmin' => 1
            ]);
        }

        $usersAccountsData = array_filter($usersAccountsData);
        DB::table('user_account')->insert($usersAccountsData);

        // Создаём привязку пользователей к новым аккаунтам, которые указаны в таблице users
        $select = DB::table('users')
            ->leftJoin('accounts', 'users.ads_account_id', '=', 'accounts.old_ads_account_id')
            ->leftJoin('user_account', 'users.id', '=', 'user_account.user_id')
            ->whereNotNull(['users.ads_account_id', 'accounts.id', 'users.id'])
            ->whereNull(['user_account.user_id', 'user_account.account_id'])
            ->select([
                DB::raw('users.id AS user_id'),
                DB::raw('accounts.id AS account_id')
            ]);
        DB::table('user_account')->insertUsing(['user_id','account_id'], $select);

        // Создаём аккаунты Озон по таблице пользователей
        $users = DB::table('users')
            ->whereNotNull(['ozon_client_id', 'ozon_client_api_key'])
            ->where('ozon_client_id', '<>', '')
            ->where('ozon_client_api_key', '<>', '')
            ->groupBy('ozon_client_id')
            ->groupBy('ozon_client_api_key')
            ->get();
        $usersAccountsData = [];

        foreach ($users as $user) {
            $usersAccountsData[] = $this->insertAccountRow([
                'platformId' => $ozonPlatformId,
                'clientId' => $user->ozon_client_id,
                'apiKey' => $user->ozon_client_api_key,
                'title' => 'Ozon',
                'isSelected' => 0,
                'oldAccountId' => null,
                'userId' => $user->id,
                'isAccountAdmin' => 0
            ]);
        }

        $users = DB::table('users')
            ->whereNotNull(['ozon_supplier_id', 'ozon_supplier_api_key'])
            ->where('ozon_supplier_id', '<>', '')
            ->where('ozon_supplier_api_key', '<>', '')
            ->groupBy('ozon_supplier_id')
            ->groupBy('ozon_supplier_api_key')
            ->get();

        foreach ($users as $user) {
            $usersAccountsData[] = $this->insertAccountRow([
                'platformId' => $ozonAdmPlatformId,
                'clientId' => $user->ozon_supplier_id,
                'apiKey' => $user->ozon_supplier_api_key,
                'title' => 'Ozon Performance',
                'isSelected' => 0,
                'oldAccountId' => null,
                'userId' => $user->id,
                'isAccountAdmin' => 0
            ]);
        }

        $usersAccountsData = array_filter($usersAccountsData);
        DB::table('user_account')->insert($usersAccountsData);

        // Ищем непривязанных пользователей к уже созданным аккаунтам
        $this->searchExistsAccountsAndAttachUsers($ozonPlatformId, 'ozon_client_id', 'ozon_client_api_key');
        $this->searchExistsAccountsAndAttachUsers($ozonAdmPlatformId, 'ozon_supplier_id', 'ozon_supplier_api_key');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('user_account')->truncate();
        DB::table('accounts')->delete();
        DB::statement('ALTER TABLE `accounts` AUTO_INCREMENT = 0;');
    }

    /**
     * Вставка нового аккаунта по переданным параметрам.
     *
     * @param array $params
     *
     * @return array массив для привязки пользователя к созданному аккаунту
     */
    public function insertAccountRow(array $params)
    {
        $existsAccountId = optional(DB::table('accounts')->where([
            ['platform_id', '=', $params['platformId']],
            ['platform_client_id', '=', $params['clientId']],
            ['platform_api_key', '=', $params['apiKey']]
        ])->first())->id;

        if (empty($existsAccountId)) {
            $newAccountId = DB::table('accounts')->insertGetId([
                'platform_id' => $params['platformId'],
                'platform_client_id' => $params['clientId'],
                'platform_api_key' => $params['apiKey'],
                'title' => $params['title'],
                'description' => null,
                'is_active' => 1,
                'params' => null,
                'old_ads_account_id' => $params['oldAccountId'] ?? null
            ]);
        } else {
            $existsAccountUser = DB::table('user_account')->where([
                ['user_id', '=', $params['userId']],
                ['account_id', '=', $existsAccountId]
            ])->exists();

            if ($existsAccountUser) {
                return [];
            }

            $newAccountId = $existsAccountId;
        }

        return [
            'user_id' => $params['userId'],
            'account_id' => $newAccountId,
            'is_account_admin' => $params['isAccountAdmin'] ?? 0,
            'is_selected' => $params['isSelected'] ?? 1
        ];
    }

    /**
     * Ищем уже созданные аккаунты и непривязанных пользователей по колонкам и выполняем привязку
     *
     * @param int $accountPlatformId id платформы для аккаунтов
     * @param string $clientIdColumnName название колонки client_id в таблице users
     * @param string $apiKeyColumnName название колонки api_key в таблице users
     */
    public function searchExistsAccountsAndAttachUsers(int $accountPlatformId, string $clientIdColumnName, string $apiKeyColumnName)
    {
        $usersSelect = DB::table('users')
            ->leftJoin('user_account', 'users.id', '=', 'user_account.user_id')
            ->join('accounts', function($join) use ($accountPlatformId, $clientIdColumnName, $apiKeyColumnName)
            {
                $join->on('users.' . $clientIdColumnName, '=', 'accounts.platform_client_id');
                $join->on('users.' . $apiKeyColumnName, '=', 'accounts.platform_api_key');
                $join->on(DB::raw($accountPlatformId), '=', 'accounts.platform_id');
            })
            ->whereNotNull(['users.' . $clientIdColumnName, 'users.' . $apiKeyColumnName])
            ->whereNull(['user_account.user_id', 'user_account.account_id'])
            ->select([
                DB::raw('users.id AS user_id'),
                DB::raw('accounts.id AS account_id')
            ]);
        DB::table('user_account')->insertUsing(['user_id','account_id'], $usersSelect);
    }
}
