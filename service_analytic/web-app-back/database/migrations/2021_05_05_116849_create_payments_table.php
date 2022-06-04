<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->foreign('recipient_id')->references('id')->on('payment_recipients');
            $table->unsignedBigInteger('abolition_reason_id')->nullable();
            $table->foreign('abolition_reason_id')->references('id')->on('abolition_reasons');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')->references('id')->on('tariffs');
            $table->unsignedBigInteger('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions');

            $table->decimal('amount');
            $table->dateTime('captured_at')->nullable();
            $table->string('status');
            $table->decimal('income_amount')->nullable();
            $table->string('currency');
            $table->string('yookassa_id');
            $table->string('description')->nullable();
            $table->boolean('test');
            $table->boolean('paid');
            $table->boolean('refundable');
            $table->json('receipt')->nullable();
            $table->json('transfers')->nullable();
            $table->string('receipt_registration');
            $table->string('idempotence_key');
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
        Schema::dropIfExists('payments');
    }
}
