<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIsSelectedColumnInAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->boolean('is_selected')->default(0)->comment('Признак текущего выбранного аккаунта для платформы');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['is_selected']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->dropColumn(['is_selected']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_selected')->default(0)->comment('Признак текущего выбранного аккаунта для платформы');
        });
    }
}
