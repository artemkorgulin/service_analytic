<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTariffActivityAddUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariff_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('order_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
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
            $table->dropForeign('tariff_activity_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
}
