<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldOptionIdToV2ProductsFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products_features', function (Blueprint $table) {
            $table->foreignId('option_id')->nullable(true)->constrained('v2_ozon_feature_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_products_features', function (Blueprint $table) {
            $table->dropColumn('option_id');
        });
    }
}
