<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CerateTableAccountInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->enum('type', ['individual', 'legal'])->comment('Физическое, юридическое лицо');
            $table->string('payment_token')->nullable()->comment('Токен-Идентификатор платежа');
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->double('balance')->nullable()->comment('баланс');
            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')->references('tariff_id')->on('tariffs');
            $table->dateTime('captured_at')->comment('Дата подключения тарифа');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_info');
    }
}
