<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBlackListBrand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_black_list', function (Blueprint $table) {
            $table->id();
            $table->string('partner')->comment('партнер');
            $table->string('brand')->comment('бренд');
            $table->string('manager')->comment('менеджер');
            $table->string('status')->comment('Статус номенклатуры');
            $table->integer('wb')->comment('wildberries');
            $table->integer('ozon')->comment('ozon');
            $table->string('pattern')->comment('паттерн для валидации');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand_black_list');
    }
}
