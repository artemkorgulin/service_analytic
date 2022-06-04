<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('Дата, за которую собрана статистика');
            $table->unsignedInteger('registrations_by_date')->default(0)->comment('Регистраций на дату');
            $table->unsignedInteger('registrations_to_date')->default(0)->comment('Регистраций всего');
            $table->unsignedInteger('verified_by_date')->default(0)->comment('Пользователей, подтвердивших регистрацию, на дату');
            $table->unsignedFloat('verified_conversion_by_date')->default(0)->comment('Пользователей, подтвердивших регистрацию, конверсия на дату, %');
            $table->unsignedInteger('verified_to_date')->default(0)->comment('Пользователей, подтвердивших регистрацию, всего');
            $table->unsignedFloat('verified_conversion_to_date')->default(0)->comment('Пользователей, подтвердивших регистрацию, конверсия всего, %');
            $table->unsignedInteger('with_account_by_date')->default(0)->comment('Пользователей добавило аккаунты, на дату');
            $table->unsignedFloat('with_account_conversion_by_date')->default(0)->comment('Пользователей добавило аккаунты, конверсия на дату, %');
            $table->unsignedInteger('with_account_to_date')->default(0)->comment('Пользователей добавило аккаунты, всего');
            $table->unsignedFloat('with_account_conversion_to_date')->default(0)->comment('Пользователей добавило аккаунты, конверсия всего, %');
            $table->unsignedInteger('payment_count_by_date')->default(0)->comment('Оплаты, на дату, шт');
            $table->unsignedInteger('payment_sum_by_date')->default(0)->comment('Оплаты, на дату, сумма');
            $table->unsignedInteger('payment_count_to_date')->default(0)->comment('Оплаты, всего, шт');
            $table->unsignedInteger('payment_sum_to_date')->default(0)->comment('Оплаты, всего, сумма');
            $table->unsignedInteger('payment_via_bank_count_by_date')->default(0)->comment('Оплаты по счету на дату');
            $table->unsignedInteger('payment_via_bank_count_to_date')->default(0)->comment('Оплаты по счету всего');
            $table->unsignedInteger('payment_via_card_count_by_date')->default(0)->comment('Оплаты по карте на дату');
            $table->unsignedInteger('payment_via_card_count_to_date')->default(0)->comment('Оплаты по карте всего');
            $table->unsignedInteger('orders_via_bank_count_by_date')->default(0)->comment('Выставлено счетов на дату');
            $table->unsignedInteger('orders_via_bank_count_to_date')->default(0)->comment('Выставлено счетов всего');
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
        Schema::dropIfExists('statistics_entries');
    }
}
