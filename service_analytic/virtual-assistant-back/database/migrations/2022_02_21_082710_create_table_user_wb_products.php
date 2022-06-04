<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserWbProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_product_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->comment('Содержит информацию с ID пользователя');
            $table->unsignedBigInteger('account_id')->comment('Содержит информацию с ID аккаунта');
            $table->unsignedBigInteger('imt_id')->comment('Содержит информацию с imt_id продукта (карточки товара)');
            $table->boolean('is_active')->default(true)->comment('Это активно или нет?!');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_product_user');
    }
}
