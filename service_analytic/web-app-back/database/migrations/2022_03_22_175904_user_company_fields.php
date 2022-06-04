<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserCompanyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->boolean('is_selected')->default(0)->comment('Признак текущей выбранной компании для пользователя');
            $table->boolean('is_active')->default(1)->comment('Признак активности пользователя в компании');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn('is_selected');
            $table->dropColumn('is_active');
        });
    }
}
