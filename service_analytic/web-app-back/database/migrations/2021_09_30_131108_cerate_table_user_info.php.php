<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CerateTableUserInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('user_info');
    }
}
