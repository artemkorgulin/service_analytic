<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTablesTarifs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tariffs', function (Blueprint $table) {
            //$table->foreign('price_id')->references('id')->on('tariff_prices');
            $table->dropForeign('tariffs_price_id_foreign');
        });
        Schema::rename('tariffs', 'old_tariffs');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::rename('old_permissions', 'permissions');
        Schema::rename('old_tariffs', 'tariffs');
    }
}
