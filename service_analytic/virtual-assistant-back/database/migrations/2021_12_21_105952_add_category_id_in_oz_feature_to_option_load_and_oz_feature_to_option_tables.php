<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdInOzFeatureToOptionLoadAndOzFeatureToOptionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('oz_category_feature_option');
        DB::table('oz_feature_to_option_load')->truncate();
        DB::table('oz_feature_to_option')->truncate();

        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_feature_to_option_load', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()
                    ->comment('Связь с категорией на случай если набор значений по характеристике уникален для каждой категории');
            }

            if (Schema::hasColumn('oz_feature_to_option_load', 'id')) {
                $table->dropColumn('id');
            }

            $table->unique(['feature_id', 'option_id', 'category_id'], 'oz_feature_to_option_load_primary');
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()
                ->comment('Связь с категорией на случай если набор значений по характеристике уникален для каждой категории');

            if (MigrationHelper::hasForeign('oz_feature_to_option', 'oz_feature_to_option_feature_id_foreign')) {
                $table->dropForeign('oz_feature_to_option_feature_id_foreign');
            }

            if (MigrationHelper::hasForeign('oz_feature_to_option', 'oz_feature_to_option_option_id_foreign')) {
                $table->dropForeign('oz_feature_to_option_option_id_foreign');
            }

            $table->unique(['feature_id', 'option_id', 'category_id'], 'oz_feature_to_option_primary');
        });

        DB::table('oz_features')->where('id', '=', 8229)->update(['is_unique_for_category' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('oz_feature_to_option_load')->truncate();
        DB::table('oz_feature_to_option')->truncate();

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            if (Schema::hasColumn('oz_feature_to_option', 'category_id')) {
                $table->dropColumn(['category_id']);
            }

            if (!MigrationHelper::hasForeign('oz_feature_to_option', 'oz_feature_to_option_feature_id_foreign')) {
                $table->foreign('feature_id', 'oz_feature_to_option_feature_id_foreign')
                    ->references('id')
                    ->on('oz_features')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (!MigrationHelper::hasForeign('oz_feature_to_option', 'oz_feature_to_option_option_id_foreign')) {
                $table->foreign('option_id', 'oz_feature_to_option_option_id_foreign')
                    ->references('id')
                    ->on('oz_feature_options')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (MigrationHelper::hasIndex('oz_feature_to_option', 'oz_feature_to_option_primary')) {
                $table->dropIndex('oz_feature_to_option_primary');
            }
        });

        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_feature_to_option_load', 'id')) {
                $table->id();
            }

            if (Schema::hasColumn('oz_feature_to_option_load', 'category_id')) {
                $table->dropColumn(['category_id']);
            }

            if (MigrationHelper::hasIndex('oz_feature_to_option_load', 'oz_feature_to_option_load_primary')) {
                $table->dropIndex('oz_feature_to_option_load_primary');
            }
        });

        Schema::create('oz_category_feature_option', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('feature_id')->index();
            $table->unsignedBigInteger('option_id');
            $table->primary(['category_id', 'feature_id', 'option_id'], 'oz_category_feature_option_primary');

            $table->foreign('category_id')->references('id')->on('oz_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('option_id')->references('id')->on('oz_feature_options')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
