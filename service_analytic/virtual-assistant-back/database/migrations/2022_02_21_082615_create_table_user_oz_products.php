<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserOzProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_product_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->comment('Содержит информацию с ID пользователя');
            $table->unsignedBigInteger('account_id')->comment('Содержит информацию с ID аккаунта');
            $table->unsignedBigInteger('external_id')->comment('Содержит информацию с external_id продукта');
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
        Schema::dropIfExists('oz_product_user');
    }
}
