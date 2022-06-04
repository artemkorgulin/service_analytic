<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzListGoodsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_list_goods_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oz_product_id')->nullable()->comment('id продукта');
            $table->string('key_request')->nullable();
            $table->integer('popularity')->nullable()->comment('Частотность');
            $table->integer('conversion')->nullable()->comment('Конверсионность');
            $table->enum('section', [1,2,3,4,5,6,7])->nullable()->comment('Приходит с фронта');
//            1 - Наименования товара,
//            2 - Ключевые слова,
//            3 - Характеристики,
//            4 - Описание,
//            5 - Ответы и отзывы,
//            6 - Название изображений,
//            7 - Рич-контент
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
        Schema::dropIfExists('oz_list_goods_users');
    }
}
