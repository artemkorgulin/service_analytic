<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableVariantAttributeValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variant_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_attribute_id')->index()->nullable(false)->comment('Ссылка на таблицу variant_attribute');
            $table->unsignedBigInteger('value_id')->index()->nullable(true)->comment('Ссылка на таблицу values');
            $table->string('value')->nullable(false)->index()->comment('Содержит значение аттрибута для варианта товара');

            $table->foreign('variant_attribute_id')->on('product_attribute')->references('id')->onDelete('cascade');
            $table->foreign('value_id')->on('values')->references('id');
        });

        \DB::statement("ALTER TABLE `variant_attribute_value` comment 'Связующая таблица между вариантами товаров и значениями их аттрибутов'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_variant_attribute_value');
    }
}
