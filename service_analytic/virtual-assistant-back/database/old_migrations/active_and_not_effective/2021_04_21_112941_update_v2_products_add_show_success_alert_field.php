<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateV2ProductsAddShowSuccessAlertField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->boolean('show_success_alert')->default(false);
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
            $table->dropColumn('show_success_alert');
        });
    }
}
