<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariff_activity', function (Blueprint $table) {
            $table->dropForeign('tariff_activity_payment_id_foreign');
            $table->dropColumn('payment_id');
            $table->unsignedBigInteger('order_id')->after('id');
            $table->tinyInteger('status')->after('tariff_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tariff_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments');

            $table->dropColumn('order_id');
            $table->dropColumn('status');
        });
    }
}
