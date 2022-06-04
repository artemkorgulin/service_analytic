<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCategoryIdInOzFeatureToOptionLoadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('oz_feature_to_option_load', 'category_id')) {
            Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }

        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            $table->text('value')->change();
        });

        if (Schema::hasColumn('oz_feature_options', 'external_id')) {
            Schema::table('oz_feature_options', function (Blueprint $table) {
                $table->dropColumn('external_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->string('value')->change();
        });

        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->unsignedBigInteger('external_id');
        });
    }
}
