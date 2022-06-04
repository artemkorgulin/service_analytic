<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdColumnInOzFeatureToOptionLoadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_features', function (Blueprint $table) {
            $table->date('oz_feature_to_option_last_sync_date')->index()->nullable()
                ->comment('Дата последней синхронизации с Озон всех значений по характеристике');
        });

        DB::table('oz_feature_to_option_load')->truncate();

        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id')->index()->comment('id характеристики');
        });

        DB::table('oz_feature_to_option')->truncate();

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('is_deleted');
            $table->dropForeign('v2_ozon_feature_to_option_option_id_foreign');
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::dropIfExists('oz_trigger_change_feature_option');

        DB::table('oz_products_features')->whereNotNull('option_id')->update(['option_id' => null]);

        Schema::table('oz_products_features', function (Blueprint $table) {
            $table->dropForeign('v2_products_features_option_id_foreign');
        });

        DB::table('oz_feature_options')->truncate();

        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->renameColumn('external_id', 'id');
            $table->primary('id');
            $table->dropUnique('v2_ozon_feature_options_external_id_unique');
        });

        Schema::table('oz_products_features', function (Blueprint $table) {
            $table->foreign('option_id')->references('id')->on('oz_feature_options')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->foreign('option_id')->references('id')->on('oz_feature_options')->onDelete('cascade')->onUpdate('cascade');
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
            $table->dropForeign('');
        });

        Schema::table('oz_features', function (Blueprint $table) {
            $table->dropColumn('oz_feature_to_option_last_sync_date');
        });

        DB::table('oz_feature_options')->truncate();

        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->dropPrimary('PRIMARY');
            $table->renameColumn('id', 'external_id');
        });

        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->primary('id');
        });

        Schema::table('oz_feature_to_option_load', function (Blueprint $table) {
            $table->dropColumn('feature_id');
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false);
            $table->id();
            $table->dropForeign('oz_feature_to_option_feature_id_foreign');
        });
    }
}
