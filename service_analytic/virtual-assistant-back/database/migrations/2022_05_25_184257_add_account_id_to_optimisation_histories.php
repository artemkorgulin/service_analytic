<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToOptimisationHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('optimisation_histories', function (Blueprint $table) {
            $table->bigInteger('account_id')->after('product_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('optimisation_histories', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
}
