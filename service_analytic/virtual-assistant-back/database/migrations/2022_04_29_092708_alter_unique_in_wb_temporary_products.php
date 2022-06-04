<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUniqueInWbTemporaryProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_temporary_products', function (Blueprint $table) {
            $table->dropUnique('wb_temporary_products_imt_id_unique');
            $table->unique(['account_id', 'imt_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_temporary_products', function (Blueprint $table) {
            $table->dropUnique('wb_temporary_products_account_id_imt_id_unique');
            $table->unique('imt_id');
        });
    }
}
