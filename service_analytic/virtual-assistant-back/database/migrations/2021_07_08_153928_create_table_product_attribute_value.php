<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductAttributeValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_attribute_id')->index()->nullable(false)->comment('Ссылка на таблицу product_attribute');
            $table->unsignedBigInteger('value_id')->index()->nullable(true)->comment('Ссылка на таблицу values');
            $table->string('value')->nullable(false)->index()->comment('Содержит значение аттрибута для товара');

            $table->foreign('product_attribute_id')->on('product_attribute')->references('id')->onDelete('cascade');
            $table->foreign('value_id')->on('values')->references('id');
        });

        \DB::statement("ALTER TABLE `product_attribute_value` comment 'Связь между аттрибутами товаров и значениями'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attribute_value');
    }
}
