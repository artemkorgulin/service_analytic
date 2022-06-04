<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueToV2OzonFeatureToOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_ozon_feature_to_option', function (Blueprint $table) {
            $table->unique(['feature_id', 'option_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_ozon_feature_to_option', function (Blueprint $table) {
            $table->dropUnique('v2_ozon_feature_to_option_feature_id_option_id_unique');
        });
    }
}
