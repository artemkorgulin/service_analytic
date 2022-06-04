<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzListGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_list_goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oz_product_id')->nullable()->comment('id продукта');
            $table->string('key_request')->nullable();
            $table->integer('popularity')->nullable()->comment('Частотность');
            $table->integer('conversion')->nullable()->comment('Конверсионность');
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
        Schema::dropIfExists('oz_list_goods');
    }
}
