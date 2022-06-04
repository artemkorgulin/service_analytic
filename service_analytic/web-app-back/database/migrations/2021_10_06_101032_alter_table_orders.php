<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()  //orders_tariff_id_foreign
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_tariff_id_foreign');
            $table->dropColumn('tariff_id');
            $table->dropColumn('order_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('order_id')->from(1000)->after('id');
            $table->json('tariff_ids')->notNullValue()->after('order_id');
            $table->json('tariff_activity_ids')->after('tariff_ids')->nullable();
        });
        DB::update("ALTER TABLE `orders` AUTO_INCREMENT = 1000");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('orders')->where('id', '>', 999)->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('tariff_id')->after('id');
            $table->foreign('tariff_id')->references('tariff_id')->on('tariffs');
            $table->dropColumn(['tariff_ids', 'tariff_activity_ids']);
        });
    }
}
