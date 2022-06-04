<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropV2OzonFeatureToOptionFeatureIdOptionIdUniqueInOzFeatureToOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            if (MigrationHelper::hasIndex('oz_feature_to_option',
                'v2_ozon_feature_to_option_feature_id_option_id_unique')) {
                $table->dropUnique('v2_ozon_feature_to_option_feature_id_option_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            if (!MigrationHelper::hasIndex('oz_feature_to_option',
                'v2_ozon_feature_to_option_feature_id_option_id_unique')) {
                $table->unique(['feature_id', 'option_id'], 'v2_ozon_feature_to_option_feature_id_option_id_unique');
            }
        });
    }
}
