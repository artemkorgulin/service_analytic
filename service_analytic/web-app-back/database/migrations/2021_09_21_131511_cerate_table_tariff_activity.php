<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CerateTableTariffActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariff_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('tariff_id');
            $table->dateTime('start_at')->comment('Дата начала');
            $table->dateTime('end_at')->comment('Дата окончания');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('tariff_id')->references('tariff_id')->on('tariffs');
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
        Schema::dropIfExists('tariff_activity');
    }
}
