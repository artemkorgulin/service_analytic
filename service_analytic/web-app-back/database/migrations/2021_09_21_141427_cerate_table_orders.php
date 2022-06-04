<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CerateTableOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('type', ['bank_card', 'bank'])->comment('Тип платежа карта или счет');

            $table->unsignedBigInteger('recipient_id');
            $table->foreign('recipient_id')->references('id')->on('payment_recipients');
            $table->unsignedBigInteger('abolition_reason_id')->nullable();
            $table->foreign('abolition_reason_id')->references('id')->on('abolition_reasons');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')->references('tariff_id')->on('tariffs');
            $table->unsignedBigInteger('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions');

            $table->decimal('amount')->comment('Сумма платежа');
            $table->decimal('income_amount')->nullable()->comment('Сумма платежа за вычетом комиссии');
            $table->dateTime('captured_at')->nullable()->comment('Время подтверждения платежа.');
            $table->string('status');
            $table->string('currency');
            $table->string('yookassa_id');
            $table->string('description')->nullable();
            $table->boolean('test')->comment('Признак тестовой операции.');
            $table->boolean('paid')->comment('Признак оплаты заказа.');
            $table->boolean('refundable')->comment('Возможность провести возврат по API');
            $table->json('receipt')->nullable();
            $table->json('transfers')->nullable();
            $table->string('merchant_customer_id')->nullable()->comment('Идентификатор покупателя в вашей системе,электронная почта или номер телефона');
            $table->string('receipt_registration')->comment('Статус доставки данных для чека в онлайн-кассу');
            $table->string('idempotence_key');
            $table->string('link')->comment('Ссылка для  скачивания');
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
        Schema::dropIfExists('orders');
    }
}
