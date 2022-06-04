<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLengthApiTokenAndInnColumnsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_api_token_unique');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 360)->nullable()->unique('users_api_token_unique')->after('password')
                ->comment('Токен пользователя для API')->change();
            $table->unsignedBigInteger('inn')->nullable()->after('ads_account_id')
                ->comment('ИНН пользователя')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_api_token_unique');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 80)->nullable()->unique('users_api_token_unique')->after('password')
                ->comment('Токен пользователя для API')->change();
            $table->integer('inn')->nullable()->after('ads_account_id')
                ->comment('ИНН пользователя')->change();
        });
    }
}
