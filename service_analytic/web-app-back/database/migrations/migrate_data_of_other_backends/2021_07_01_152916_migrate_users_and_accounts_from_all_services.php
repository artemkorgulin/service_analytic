<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use AnalyticPlatform\LaravelHelpers\Helpers\CollectionHelper;
use Carbon\Carbon;

/**
 * Class MigrateUsersAndAccountsFromAllServices
 *
 * Миграция и объединение пользователей со всех серверов и баз на web-app
 */
class MigrateUsersAndAccountsFromAllServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Слияние пользователей из базы Virtual Assistent
        Schema::connection('mysql')->table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('va_old_user_id')->nullable()->after('inn')
                ->comment('Id пользователя VA для экспорта со старого сервера');
        });
        $usersVA = DB::connection('va')->table('users')->select([
            'name',
            'email',
            'email_verified_at',
            'login',
            'password',
            'api_token',
            'remember_token',
            'created_at',
            'updated_at',
            'verification_token',
            DB::raw('0 as enable_email_notifications'),
            'ozon_client_id',
            DB::raw('ozon_api_key AS ozon_client_api_key'),
            'ozon_supplier_id',
            'ozon_supplier_api_key',
            DB::raw('null as virtual_assistant_token'),
            DB::raw('null as bids_manager_token'),
            DB::raw('0 as first_login'),
            DB::raw('null as ads_account_id'),
            DB::raw('null as inn'),
            DB::raw('id AS va_old_user_id')
        ])->get();
        $this->createNewUsers($usersVA, 'va_old_user_id');

        // Обновление user_id в базе VA
        $this->createTempUserTable('va', 'va_old_user_id');
        $this->updateUserIdInTable('va', 'oz_temporary_products');
        $this->updateUserIdInTable('va', 'oz_products');
        Schema::connection('mysql')->table('users', function (Blueprint $table) {
            $table->dropColumn(['va_old_user_id']);
        });
        Schema::connection('va')->dropIfExists('temp_web_app_user_ids');

        // Слияние пользователей из базы Adm
        Schema::connection('mysql')->table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('ads_old_user_id')->nullable()->after('inn')
                ->comment('Id пользователя ADM для экспорта со старого сервера');
        });
        $usersAdm = DB::connection('adm')->table('users')->select([
            'name',
            'email',
            'email_verified_at',
            DB::raw('null as login'),
            'password',
            'api_token',
            'remember_token',
            'created_at',
            'updated_at',
            DB::raw('null as verification_token'),
            DB::raw('0 as enable_email_notifications'),
            DB::raw('null as ozon_client_id'),
            DB::raw('null as ozon_client_api_key'),
            DB::raw('null as ozon_supplier_id'),
            DB::raw('null as ozon_supplier_api_key'),
            DB::raw('null as virtual_assistant_token'),
            DB::raw('null as bids_manager_token'),
            DB::raw('0 as first_login'),
            DB::raw('account_id as ads_account_id'),
            'inn',
            DB::raw('id AS ads_old_user_id')
        ])->get();
        $this->createNewUsers($usersAdm, 'ads_old_user_id');

        // Обновление user_id в базе ADM
        $this->createTempUserTable('adm', 'ads_old_user_id');
        $this->updateUserIdInTable('adm', 'accounts');
        $this->updateUserIdInTable('adm', 'campaigns');
        Schema::connection('mysql')->table('users', function (Blueprint $table) {
            $table->dropColumn(['ads_old_user_id']);
        });
        Schema::connection('adm')->dropIfExists('temp_web_app_user_ids');

        DB::table('users')->whereNull('created_at')->update(['created_at' => Carbon::now()]);
        DB::table('users')->whereNull('updated_at')->update(['updated_at' => Carbon::now()]);
    }

    /**
     * Создаём новых пользователей в базе Web-app
     *
     * @param $newUsersData
     * @param $oldUserIdColumnName
     */
    public function createNewUsers($newUsersData, $oldUserIdColumnName)
    {
        $users = DB::connection('mysql')->table('users')->get();
        $newUsers = [];

        foreach ($newUsersData as $user) {
            $userExists = $users->where('email', '=', $user->email)->first();

            if (empty($userExists)) {
                $newUsers[] = (array) $user;
            } else {
                DB::connection('mysql')->table('users')->where('id', $userExists->id)->update([$oldUserIdColumnName => $user->$oldUserIdColumnName]);
            }
        }

        // Создание новых пользователей в Web-app
        $lastId = DB::connection('mysql')->table('users')->latest('id')->first()->id;
        DB::connection('mysql')->table('users')->insert($newUsers);
        $this->createSubscriptions($lastId);
    }

    /**
     * Создаём временную таблицу со старыми и новыми id пользователей для обновления связанных таблиц
     *
     * @param $connectionName
     * @param $columnOldUserIdName
     */
    public function createTempUserTable($connectionName, $columnOldUserIdName)
    {
        Schema::connection($connectionName)->create('temp_web_app_user_ids', function (Blueprint $table) {
            $table->unsignedBigInteger('old_user_id');
            $table->unsignedBigInteger('new_user_id');
        });
        $userTempIds = DB::connection('mysql')->table('users')->whereNotNull($columnOldUserIdName)
            ->select([DB::raw('id AS new_user_id'), DB::raw($columnOldUserIdName . ' AS old_user_id')])->get();
        $userTempIds = CollectionHelper::convertCollectionWithStdClassToArray($userTempIds);
        DB::connection($connectionName)->table('temp_web_app_user_ids')->insert($userTempIds);
    }

    /**
     * Создаём базовые подписки для всех новых пользователей
     *
     * @param int $lastId id пользователя с которого начнётся создание подписок
     */
    public function createSubscriptions($lastId)
    {
        $newSubscriptions = DB::connection('mysql')->table('users')->where('id', '>', $lastId)->select([
            DB::raw('id AS user_id'),
            DB::raw('1 AS tariff_id'),
            DB::raw('1 AS quantity'),
            DB::raw('NOW() AS activated_at'),
            DB::raw('NOW() + INTERVAL 3 YEAR AS end_at'),
            DB::raw('"Активна" AS status'),
            DB::raw('NOW() AS created_at'),
            DB::raw('NOW() AS updated_at'),
        ])->get();
        $newSubscriptions = CollectionHelper::convertCollectionWithStdClassToArray($newSubscriptions);
        DB::connection('mysql')->table('subscriptions')->insert($newSubscriptions);
    }

    /**
     * Обновление user_id со старого на новый в указанной таблице и сервере
     *
     * @param $connectionName
     * @param $tableName
     */
    public function updateUserIdInTable($connectionName, $tableName)
    {
        DB::connection($connectionName)->update(<<<SQL
            update `$tableName` as `source`
            left join `temp_web_app_user_ids` as `temp` on `source`.`user_id` = `temp`.`old_user_id`
            set `source`.`user_id` = `temp`.`new_user_id`
SQL
        );
    }
}
