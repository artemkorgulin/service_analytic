<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestOptionToV2ProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->comment('Тестовый товар');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
}
