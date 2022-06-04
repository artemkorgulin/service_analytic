<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAttributeValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_value', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id')->index()->nullable(false)->comment('Ссылка на таблицу attributes');
            $table->unsignedBigInteger('value_id')->index()->nullable(false)->comment('Ссылка на таблицу values');

            $table->foreign('attribute_id')->on('attributes')->references('id')->onDelete('cascade');
            $table->foreign('value_id')->on('values')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `attribute_value` comment 'Связующая таблица между аттрибутами и их значениями'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_value');
    }
}
