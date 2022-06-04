<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCampaignGoodsAndCampaignGoodStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::hasForeign('campaign_good_statistics',
            'campaign_good_statistics_campaign_good_id_foreign')) {
            Schema::table('campaign_good_statistics', function (Blueprint $table) {
                $table->dropForeign('campaign_good_statistics_campaign_good_id_foreign');
            });
        }

        Schema::rename('campaign_goods', 'campaign_product');

        Schema::table('campaign_product', function (Blueprint $table) {
            $table->renameColumn('good_id', 'product_id');

            if (MigrationHelper::hasForeign('campaign_product', 'campaign_goods_campaign_id_foreign')) {
                $table->dropForeign('campaign_goods_campaign_id_foreign');
            }

            if (MigrationHelper::hasForeign('campaign_product', 'campaign_goods_group_id_foreign')) {
                $table->dropForeign('campaign_goods_group_id_foreign');
            }

            if (MigrationHelper::hasForeign('campaign_product', 'campaign_goods_status_id_foreign')) {
                $table->dropForeign('campaign_goods_status_id_foreign');
            }
        });

        Schema::table('campaign_product', function (Blueprint $table) {
            if (MigrationHelper::hasIndex('campaign_product', 'good_status_id_foreign')) {
                $table->dropIndex('good_status_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_good_id_foreign')) {
                $table->dropIndex('campaign_goods_good_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_group_id_foreign')) {
                $table->dropIndex('campaign_goods_group_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'good_campaign_id_index')) {
                $table->dropIndex('good_campaign_id_index');
                $table->index('campaign_id');
            }

            if (MigrationHelper::hasIndex('campaign_product',
                'campaign_goods_id_campaign_id_group_id_status_id_index')) {
                $table->dropIndex('campaign_goods_id_campaign_id_group_id_status_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_id_campaign_id_group_id_index')) {
                $table->dropIndex('campaign_goods_id_campaign_id_group_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_id_campaign_id_status_id_index')) {
                $table->dropIndex('campaign_goods_id_campaign_id_status_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_id_campaign_id_index')) {
                $table->dropIndex('campaign_goods_id_campaign_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_id_status_id_index')) {
                $table->dropIndex('campaign_goods_id_status_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_campaign_id_status_id_index')) {
                $table->dropIndex('campaign_goods_campaign_id_status_id_index');
                $table->index(['campaign_id', 'status_id']);
            }

            if (MigrationHelper::hasIndex('campaign_product', 'campaign_goods_campaign_id_group_id_index')) {
                $table->dropIndex('campaign_goods_campaign_id_group_id_index');
                $table->index(['campaign_id', 'group_id']);
            }

            $table->foreign('campaign_id')->references('id')
                ->on('campaigns')
                ->cascadeOnUpdate()
                ->onDelete('restrict');

            $table->foreign('group_id')->references('id')
                ->on('groups')
                ->cascadeOnUpdate()
                ->onDelete('restrict');

            $table->foreign('status_id')->references('id')
                ->on('statuses')
                ->cascadeOnUpdate()
                ->onDelete('restrict');
        });

        Schema::rename('campaign_good_statistics', 'campaign_product_statistic');

        Schema::table('campaign_product_statistic', function (Blueprint $table) {
            $table->renameColumn('campaign_good_id', 'campaign_product_id');

            if (MigrationHelper::hasIndex('campaign_product_statistic', 'good_statistics_good_id_foreign')) {
                $table->dropIndex('good_statistics_good_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_product_statistic', 'campaign_good_statistics_date_index')) {
                $table->dropIndex('campaign_good_statistics_date_index');
                $table->index('date');
            }

            if (MigrationHelper::hasIndex('campaign_product_statistic',
                'campaign_good_statistics_date_campaign_good_id_index')) {
                $table->dropIndex('campaign_good_statistics_date_campaign_good_id_index');
                $table->index(['date', 'campaign_product_id']);
            }
        });

        Schema::table('campaign_product_statistic', function (Blueprint $table) {
            $table->foreign('campaign_product_id')->references('id')
                ->on('campaign_product')
                ->cascadeOnUpdate()
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (MigrationHelper::hasForeign('campaign_product_statistic',
            'campaign_product_statistic_campaign_product_id_foreign')) {
            Schema::table('campaign_product_statistic', function (Blueprint $table) {
                $table->dropForeign('campaign_product_statistic_campaign_product_id_foreign');
            });
        }

        Schema::rename('campaign_product', 'campaign_goods');

        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->renameColumn('product_id', 'good_id');

            if (MigrationHelper::hasForeign('campaign_goods', 'campaign_product_campaign_id_foreign')) {
                $table->dropForeign('campaign_product_campaign_id_foreign');
            }

            if (MigrationHelper::hasForeign('campaign_goods', 'campaign_product_group_id_foreign')) {
                $table->dropForeign('campaign_product_group_id_foreign');
            }

            if (MigrationHelper::hasForeign('campaign_goods', 'campaign_product_status_id_foreign')) {
                $table->dropForeign('campaign_product_status_id_foreign');
            }
        });

        Schema::table('campaign_goods', function (Blueprint $table) {
            if (MigrationHelper::hasIndex('campaign_goods', 'campaign_product_status_id_foreign')) {
                $table->dropIndex('campaign_product_status_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_goods', 'campaign_product_group_id_foreign')) {
                $table->dropIndex('campaign_product_group_id_foreign');
            }

            if (MigrationHelper::hasIndex('campaign_goods', 'campaign_product_campaign_id_index')) {
                $table->dropIndex('campaign_product_campaign_id_index');
                $table->index('campaign_id', 'good_campaign_id_index');
            }

            if (!MigrationHelper::hasIndex('campaign_goods',
                'campaign_goods_id_campaign_id_group_id_status_id_index')) {
                $table->index(['id', 'campaign_id', 'group_id', 'status_id'],
                    'campaign_goods_id_campaign_id_group_id_status_id_index');
            }

            if (!MigrationHelper::hasIndex('campaign_goods', 'campaign_goods_id_campaign_id_group_id_index')) {
                $table->index(['id', 'campaign_id', 'group_id'], 'campaign_goods_id_campaign_id_group_id_index');
            }

            if (!MigrationHelper::hasIndex('campaign_goods', 'campaign_goods_id_campaign_id_status_id_index')) {
                $table->index(['id', 'campaign_id', 'status_id'], 'campaign_goods_id_campaign_id_status_id_index');
            }

            if (!MigrationHelper::hasIndex('campaign_goods', 'campaign_goods_id_campaign_id_index')) {
                $table->index(['id', 'campaign_id'], 'campaign_goods_id_campaign_id_index');
            }

            if (!MigrationHelper::hasIndex('campaign_goods', 'campaign_goods_id_status_id_index')) {
                $table->index(['id', 'status_id'], 'campaign_goods_id_status_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_goods', 'campaign_product_campaign_id_status_id_index')) {
                $table->dropIndex('campaign_product_campaign_id_status_id_index');
                $table->index(['campaign_id', 'status_id'], 'campaign_goods_campaign_id_status_id_index');
            }

            if (MigrationHelper::hasIndex('campaign_goods', 'campaign_product_campaign_id_group_id_index')) {
                $table->dropIndex('campaign_product_campaign_id_group_id_index');
                $table->index(['campaign_id', 'group_id'], 'campaign_goods_campaign_id_group_id_index');
            }

            $table->foreign('campaign_id')->references('id')
                ->on('campaigns')
                ->cascadeOnUpdate()
                ->onDelete('restrict');

            $table->foreign('group_id')->references('id')
                ->on('groups')
                ->cascadeOnUpdate()
                ->onDelete('restrict');

            $table->foreign('status_id')->references('id')
                ->on('statuses')
                ->cascadeOnUpdate()
                ->onDelete('restrict');
        });

        Schema::rename('campaign_product_statistic', 'campaign_good_statistics');

        Schema::table('campaign_good_statistics', function (Blueprint $table) {
            $table->renameColumn('campaign_product_id', 'campaign_good_id');

            if (MigrationHelper::hasIndex('campaign_good_statistics', 'campaign_product_statistic_date_index')) {
                $table->dropIndex('campaign_product_statistic_date_index');
                $table->index('date', 'campaign_good_statistics_date_index');
            }

            if (MigrationHelper::hasIndex('campaign_good_statistics',
                'campaign_product_statistic_date_campaign_product_id_index')) {
                $table->dropIndex('campaign_product_statistic_date_campaign_product_id_index');
                $table->index(['date', 'campaign_product_id'], 'campaign_good_statistics_date_campaign_good_id_index');
            }
        });

        Schema::table('campaign_good_statistics', function (Blueprint $table) {
            $table->foreign('campaign_good_id')->references('id')
                ->on('campaign_goods')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
}
