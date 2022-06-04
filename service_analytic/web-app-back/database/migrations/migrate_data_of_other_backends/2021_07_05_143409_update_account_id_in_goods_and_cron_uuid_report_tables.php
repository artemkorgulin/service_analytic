<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use AnalyticPlatform\LaravelHelpers\Helpers\CollectionHelper;

/**
 * Class UpdateAccountIdInGoodsAndCronUuidReportTables
 * Обновление account_id в таблицах goods и cron_uuid_report
 */
class UpdateAccountIdInGoodsAndCronUuidReportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('adm')->table('goods', function (Blueprint $table) {
            $table->dropForeign('goods_account_id_foreign');
        });

        Schema::connection('adm')->table('cron_uuid_report', function (Blueprint $table) {
            $table->dropForeign('cron_uuid_report_account_id_foreign');
        });

        Schema::connection('adm')->create('temp_web_app_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('platform_title');
            $table->string('platform_client_id')->nullable();
            $table->string('platform_api_key', 500)->nullable();
            $table->unsignedBigInteger('old_ads_account_id')->nullable();
        });
        $accountsData = DB::connection('mysql')->table('accounts')
            ->leftJoin('platforms', 'accounts.platform_id', '=', 'platforms.id')
            ->select(['accounts.id', 'platforms.title AS platform_title', 'platform_client_id', 'platform_api_key', 'old_ads_account_id'])->get();
        $accountsData = CollectionHelper::convertCollectionWithStdClassToArray($accountsData);
        DB::connection('adm')->table('temp_web_app_accounts')->insert($accountsData);

        DB::connection('adm')->update(<<<SQL
            UPDATE cron_uuid_report cur 
            LEFT JOIN accounts a ON cur.account_id = a.id
            LEFT JOIN temp_web_app_accounts twaa ON a.perfomance_client_id = twaa.platform_client_id AND a.perfomance_client_secret = twaa.platform_api_key
            SET cur.account_id = twaa.id
            WHERE twaa.platform_title = 'Ozon Performance' AND twaa.id <> a.id
SQL
        );

        DB::connection('adm')->update(<<<SQL
            UPDATE goods g 
            LEFT JOIN accounts a ON g.account_id = a.id
            LEFT JOIN temp_web_app_accounts twaa ON a.seller_client_id = twaa.platform_client_id AND a.seller_api_key = twaa.platform_api_key
            SET g.account_id = twaa.id
            WHERE twaa.platform_title = 'Ozon' AND twaa.id <> a.id
SQL
        );

        Schema::connection('adm')->dropIfExists('temp_web_app_accounts');
    }
}
