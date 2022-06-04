<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use AnalyticPlatform\LaravelHelpers\Helpers\CollectionHelper;

class UpdateAccountIdInCampagnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('adm')->dropIfExists('temp_web_app_accounts');
        Schema::connection('adm')->create('temp_web_app_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('platform_title');
            $table->string('platform_client_id')->nullable();
            $table->string('platform_api_key', 500)->nullable();
            $table->unsignedBigInteger('old_ads_account_id')->nullable();
        });
        $accountsData = DB::connection('mysql')->table('accounts')
            ->leftJoin('platforms', 'accounts.platform_id', '=', 'platforms.id')
            ->select([
                'accounts.id', 'platforms.title AS platform_title', 'platform_client_id', 'platform_api_key',
                'old_ads_account_id'
            ])->get();
        $accountsData = CollectionHelper::convertCollectionWithStdClassToArray($accountsData);
        DB::connection('adm')->table('temp_web_app_accounts')->insert($accountsData);

        DB::connection('adm')->update(<<<SQL
            UPDATE campaigns c 
            LEFT JOIN old_accounts a ON c.account_id = a.id
            LEFT JOIN temp_web_app_accounts twaa ON a.perfomance_client_id = twaa.platform_client_id AND a.perfomance_client_secret = twaa.platform_api_key
            SET c.account_id = twaa.id
            WHERE twaa.platform_title = 'Ozon Performance'
SQL
        );

        Schema::connection('adm')->dropIfExists('temp_web_app_accounts');
    }
}
