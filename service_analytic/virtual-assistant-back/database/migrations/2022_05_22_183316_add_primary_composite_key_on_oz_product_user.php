<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryCompositeKeyOnOzProductUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_product_user', function (Blueprint $table) {
            $table->primary(['account_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_product_user', function (Blueprint $table) {
            $table->dropPrimary(['account_id', 'external_id']);
        });
    }
}
