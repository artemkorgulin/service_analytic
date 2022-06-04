<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description')->nullable();
            $table->string('api_url')->nullable()->unique();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        DB::table('platforms')->insert([
            ['id' => 1, 'title' => 'Ozon', 'description' => 'API для работы с товарами и продажами', 'api_url' => 'https://api-seller.ozon.ru'],
            ['id' => 2, 'title' => 'Ozon Performance', 'description' => 'API для управления рекламой', 'api_url' => 'https://performance.ozon.ru:443'],
            ['id' => 3, 'title' => 'Wildberries', 'description' => null, 'api_url' => 'https://suppliers-api.wildberries.ru'],
            ['id' => 4, 'title' => 'AliExpress', 'description' => null, 'api_url' => 'https://api.taobao.com/router/rest'],
            ['id' => 5, 'title' => 'Amazon', 'description' => null, 'api_url' => 'https://api.amazon.com'],
        ]);

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('platform_id');
            $table->string('platform_client_id')->nullable()->comment('Идентификатор пользователя на платформе');
            $table->string('platform_api_key', 500)->nullable()->comment('API ключ пользователя на платформе');
            $table->string('title')->default('Основной')->comment('Название аккаунта');
            $table->string('description')->nullable()->comment('Описание аккаунта');
            $table->boolean('is_selected')->default(0)->comment('Признак текущего выбранного аккаунта для платформы');
            $table->boolean('is_active')->default(1)->comment('Признак активности аккаунта');
            $table->json('params')->nullable()->comment('Прочие параметры аккаунта необходимые для работы с API');
            $table->unsignedBigInteger('old_ads_account_id')->nullable()
                ->comment('Старый id из базы ADM. Нужен только на время перехода на общие таблицы аккаунтов');
            $table->timestamps();

            $table->foreign('platform_id')->on('platforms')->references('id')->onDelete('restrict');
            $table->unique(['platform_id', 'platform_client_id', 'platform_api_key']);
        });

        Schema::create('user_account', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->comment('Ссылка на таблицу users');
            $table->unsignedBigInteger('account_id')->comment('Ссылка на таблицу accounts');
            $table->boolean('is_account_admin')->default(0)->comment('Пользователь может редактировать аккаунт');

            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreign('account_id')->on('accounts')->references('id')->onDelete('cascade');
            $table->unique(['user_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_account');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('platforms');
    }
}
