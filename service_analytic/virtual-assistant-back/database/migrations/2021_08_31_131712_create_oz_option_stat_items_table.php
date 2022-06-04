<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzOptionStatItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_option_stat_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_stat_id')->nullable(false)->index()->comment('Поле для foreign key');
            $table->string('category')->nullable(true)->comment('Категория');
            $table->string('key_request')->nullable(false)->index()->comment('Ключевой запрос');
            $table->string('search_response')->nullable(false)->index()->comment('Поисковая выдача');
            $table->integer('popularity')->nullable(false)->default(0)->index()->comment('Популярность запроса');
            $table->integer('add_to_cart')->nullable(true)->default(0)->comment('Добавления в корзину');
            $table->decimal('conversion', 8,2)->nullable()->default(0)->default(0.0)->comment('Конверсия в корзину');
            $table->decimal('average_price', 8,2)->nullable()->default(0)->comment('Средняя стоимость');
            $table->string('search_date')->nullable()->comment('Дата поиска');
            $table->string('parsing_datetime')->nullable()->comment('Дата и время парсинга');
            $table->timestamps();

            $table->foreign('option_stat_id')->on('oz_option_stats')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_option_stat_items');
    }
}
