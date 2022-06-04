<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzProductChangeHistoryStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_product_change_history_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('history_id')->index()->nullable(false)->comment('foreign key для oz_product_change_history');
            $table->json('response_data')->nullable()->comment('Для хранения данных по ответу и Ozon');
            $table->json('request_data')->nullable()->comment('Для хранения данных по запросу в Ozon');
            $table->string('text_status')->nullable()->comment('Текстовый статус Ozon');
            $table->unsignedBigInteger('status_id')->index()->nullable()->comment('Статус наш преобразованный из текста статуса Ozon');
            $table->timestamps();

            $table->foreign('history_id')->on('oz_product_change_history')->references('id')->onDelete('cascade');
            $table->foreign('status_id')->on('oz_product_statuses')->references('id')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_product_change_history_statuses');
    }
}
