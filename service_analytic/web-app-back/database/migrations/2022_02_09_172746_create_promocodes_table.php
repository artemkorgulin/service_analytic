<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocodes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Промокод');
            $table->dateTime('start_at')->comment('Дата начала');
            $table->dateTime('end_at')->comment('Дата окончания');
            $table->integer('usage_limit')
                  ->comment('Количество возможных использований данного промокода');
            $table->boolean('multiple_uses')
                  ->default(false)
                  ->comment('Ограничение по использованию промокода пользователем');
            $table->integer('type')->comment('Тип промокода');
            $table->integer('type_c')
                  ->comment('Количественный параметр (% скидки, количество дней бонуса,'.
                            ' количество добавляемых SKU, количество единиц для пополнения баланса');
            $table->boolean('active');
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
        Schema::dropIfExists('promocodes');
    }
}
