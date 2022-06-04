<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetNullableCategoryIdV2OzonFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->dropForeign('v2_ozon_features_category_id_foreign');
        });
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(true)
                ->change()->constrained('v2_ozon_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->dropForeign('v2_ozon_features_category_id_foreign');
        });
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)
                ->change()->constrained('v2_ozon_categories');
        });
    }
}
