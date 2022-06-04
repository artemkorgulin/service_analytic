<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexFeatureCategoryInOzFeatureToOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->index(['feature_id', 'category_id']);
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
            $table->dropIndex('oz_feature_to_option_feature_id_category_id_index');
        });
    }
}
