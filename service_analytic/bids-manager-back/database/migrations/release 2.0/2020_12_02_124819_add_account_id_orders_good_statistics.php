<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdOrdersGoodStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good_statistics', function (Blueprint $table) {
            $table->integer('account_id')->nullable();
            $table->integer('orders')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('good_statistics', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('orders');
        });
    }
}
