<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('attribute_id')->nullable(false)->unsigned()->index()
                ->comment('Для отношения аттрибута');
            $table->string('value')->nullable(false)->index()->comment('Значение аттрибута');

            $table->foreign('attribute_id')->on('attributes')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `values` comment 'Таблица значений аттрибутов товара'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('values');
    }
}
