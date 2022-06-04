<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryCompositeKeyOnWbProductUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_product_user', function (Blueprint $table) {
            $table->primary(['account_id', 'imt_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_product_user', function (Blueprint $table) {
            $table->dropPrimary(['account_id', 'imt_id']);
        });
    }
}
