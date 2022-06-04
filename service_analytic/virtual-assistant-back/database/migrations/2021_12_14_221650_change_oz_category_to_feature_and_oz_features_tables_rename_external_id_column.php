<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeOzCategoryToFeatureAndOzFeaturesTablesRenameExternalIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('oz_feature_to_option_load')->truncate();
        DB::table('oz_feature_to_option')->truncate();
        DB::table('oz_products_features')->whereNotNull('option_id')->update(['option_id' => null]);
        DB::table('oz_feature_options')->truncate();
        DB::table('oz_category_to_feature')->truncate();
        DB::table('oz_product_feature_error_history')->truncate();
        DB::table('oz_product_feature_history')->truncate();
        DB::table('oz_product_change_history_statuses')->truncate();
        DB::table('oz_product_change_history')->truncate();
        DB::table('oz_products_features')->truncate();
        DB::table('oz_features')->truncate();
        Schema::enableForeignKeyConstraints();
        Schema::dropIfExists('oz_trigger_change_feature');

        Schema::table('oz_category_to_feature', function (Blueprint $table) {
            if (Schema::hasColumn('oz_category_to_feature', 'id')) {
                $table->dropColumn('id');
            }

            $table->primary(['category_id', 'feature_id']);

            if (Schema::hasColumn('oz_category_to_feature', 'oz_feature_to_option_last_sync_date')) {
                $table->date('oz_feature_to_option_last_sync_date')->nullable()
                    ->comment('Дата последней синхронизации с Озон всех значений по характеристике категории')
                    ->change();
            } else {
                $table->date('oz_feature_to_option_last_sync_date')->index()->nullable()
                    ->comment('Дата последней синхронизации с Озон всех значений по характеристике категории');
            }

            if (Schema::hasColumn('oz_category_to_feature', 'count_values')) {
                $table->integer('count_values')->default(0)
                    ->comment('Число значений в характеристике для категории')->change();
            } else {
                $table->integer('count_values')->default(0)
                    ->comment('Число значений в характеристике для категории');
            }

            if (Schema::hasColumn('oz_category_to_feature', 'old_count_values')) {
                $table->integer('old_count_values')->default(0)
                    ->comment('Прошлое число значений в характеристике для категории')->change();
            } else {
                $table->integer('old_count_values')->default(0)
                    ->comment('Прошлое число значений в характеристике для категории');
            }
        });

        Schema::table('oz_product_feature_error_history', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_product_feature_error_history',
                'v2_product_feature_error_history_history_id_foreign')) {
                $table->dropForeign('v2_product_feature_error_history_history_id_foreign');
            }

            if (MigrationHelper::hasForeign('oz_product_feature_error_history',
                'v2_product_feature_error_history_feature_id_foreign')) {
                $table->dropForeign('v2_product_feature_error_history_feature_id_foreign');
            }
        });

        Schema::table('oz_product_feature_history', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_product_feature_history', 'v2_product_feature_history_feature_id_foreign')) {
                $table->dropForeign('v2_product_feature_history_feature_id_foreign');
            }
        });

        Schema::table('oz_products_features', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_products_features', 'v2_products_features_feature_id_foreign')) {
                $table->dropForeign('v2_products_features_feature_id_foreign');
            }
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_feature_to_option', 'v2_ozon_feature_to_option_feature_id_foreign')) {
                $table->dropForeign('v2_ozon_feature_to_option_feature_id_foreign');
            }
        });

        Schema::table('oz_category_to_feature', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_category_to_feature', 'v2_ozon_category_to_feature_feature_id_foreign')) {
                $table->dropForeign('v2_ozon_category_to_feature_feature_id_foreign');
            }
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_feature_to_option', 'oz_feature_to_option_feature_id_foreign')) {
                $table->dropForeign('oz_feature_to_option_feature_id_foreign');
            }
        });

        Schema::dropIfExists('oz_trigger_change_feature_option');

        Schema::disableForeignKeyConstraints();
        Schema::table('oz_features', function (Blueprint $table) {
            if (Schema::hasColumn('oz_features', 'id')) {
                $table->dropColumn('id');
            }

            if (Schema::hasColumn('oz_features', 'external_id')) {
                $table->renameColumn('external_id', 'id');
            }

            $table->primary(['id']);
        });
        Schema::enableForeignKeyConstraints();

        Schema::table('oz_product_feature_error_history', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('oz_product_feature_history', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('oz_products_features', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::table('oz_feature_to_option', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('oz_category_to_feature', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
