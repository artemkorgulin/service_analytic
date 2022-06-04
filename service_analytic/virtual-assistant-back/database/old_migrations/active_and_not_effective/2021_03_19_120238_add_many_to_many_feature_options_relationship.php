<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManyToManyFeatureOptionsRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_ozon_feature_options', function (Blueprint $table) {
            $table->dropForeign('v2_ozon_feature_options_category_id_foreign');
            $table->dropColumn('category_id');
            $table->dropForeign('v2_ozon_feature_options_feature_id_foreign');
            $table->dropColumn('feature_id');
            $table->dropColumn('is_deleted');
            $table->unique('external_id');
        });

        Schema::create('v2_ozon_feature_to_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('v2_ozon_feature_options');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
            $table->boolean('is_deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_ozon_feature_to_option');

        Schema::table('v2_ozon_feature_options', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->foreignId('feature_id')->constrained('v2_ozon_features');
            $table->boolean('is_deleted')->default(0);
            $table->dropUnique('v2_ozon_feature_options_external_id_unique');
        });
    }
}
