<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnWbProductNameFromSemanticRufago extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->renameColumn('WB_product_name', 'wb_product_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->renameColumn('wb_product_name', 'WB_product_name');
        });
    }
}
