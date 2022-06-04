<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexies extends Migration
{
    /**
     * Run the migrations.
     *
     *TODO Применить индексы
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_goods', function (Blueprint $table) {
//            $table->index(['id', 'campaign_id', 'group_id', 'status_id']);
//            $table->index(['id', 'campaign_id', 'group_id']);
///           $table->index(['id', 'campaign_id', 'status_id']);
//            $table->index(['id', 'campaign_id']);
//            $table->index(['id', 'status_id']);
//            $table->index(['campaign_id', 'status_id']);
//            $table->index(['campaign_id', 'group_id']);
//            $table->index(['campaign_id', 'good_id']);
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
//            $table->index(['campaign_good_id', 'group_id']);
//            $table->index(['campaign_good_id', 'keyword_id']);
//            $table->index(['campaign_good_id', 'status_id']);
        });

        Schema::table('goods', function (Blueprint $table) {
//            $table->index(['price']);
        });

        Schema::table('campaign_statistics', function (Blueprint $table) {
//            $table->dropIndex('date');
//            $table->dropIndex('date_index');
//            $table->dropIndex('cs_index');
//            $table->dropIndex('idx_campaign_statistics_campaign_id');
//            $table->dropIndex('idx_campaign_statistics_consumption');
//            $table->dropIndex('idx_campaign_statistics_views');
//            $table->dropIndex('idx_campaign_statistics_clicks');
//            $table->dropIndex('idx_campaign_statistics_id');
//            $table->index(['date', 'campaign_id']);
        });

        Schema::table('campaign_good_statistics', function (Blueprint $table) {
            $table->index(['date']);
            $table->index(['date', 'campaign_good_id']);
        });

        Schema::table('campaign_keyword_statistics', function (Blueprint $table) {
            $table->index(['date']);
            $table->index(['date', 'campaign_keyword_id']);
        });

        Schema::table('strategies', function (Blueprint $table) {
            $table->index(['strategy_type_id', 'strategy_status_id']);
        });

        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->index(['date']);
            $table->index(['date', 'strategy_id']);
            $table->index(['date', 'campaign_keyword_id']);
            $table->index(['date', 'strategy_id', 'campaign_keyword_id'], 'strategy_keyword_statistics_by_keyword_on_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['inn']);
            $table->bigInteger('account_id')->nullable()->unsigned()->change();
            $table->foreign('account_id')->references('id')->on('accounts')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->unsigned()->change();
            $table->foreign('user_id')->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
