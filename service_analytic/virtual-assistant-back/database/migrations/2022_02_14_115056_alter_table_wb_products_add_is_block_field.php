<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsAddIsBlockField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->boolean('is_block')->nullable()->comment('Защита от одновременного редактирования');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->dropColumn('is_block');
        });
    }
}
