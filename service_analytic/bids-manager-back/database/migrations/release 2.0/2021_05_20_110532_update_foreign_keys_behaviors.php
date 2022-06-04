<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Output\ConsoleOutput;

class UpdateForeignKeysBehaviors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $output = new ConsoleOutput();

        $output->writeln('campaign_goods');
        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->dropForeign('campaign_goods_campaign_id_foreign');
            $table->foreign('campaign_id')->on('campaigns')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        $output->writeln('campaign_good_statistics');
        Schema::table('campaign_good_statistics', function (Blueprint $table) {
            $table->dropForeign('good_statistics_good_id_foreign');
            $table->foreign('campaign_good_id', '')->on('campaign_goods')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        $output->writeln('campaign_keywords');
        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropForeign('campaign_keywords_campaign_good_id_foreign');
            $table->foreign('campaign_good_id')->on('campaign_goods')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->dropForeign('keyword_status_id_foreign');
            $table->foreign('status_id')->on('statuses')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        $output->writeln('campaign_keyword_statistics');
        Schema::table('campaign_keyword_statistics', function (Blueprint $table) {
            //$table->dropForeign('keyword_statistics_keyword_id_foreign');
            $table->foreign('campaign_keyword_id')->on('campaign_keywords')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        $output->writeln('campaign_statistics');
        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropForeign('campaign_statistics_campaign_id_foreign');
            $table->foreign('campaign_id')->on('campaigns')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        $output->writeln('campaign_stop_words');
        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->dropForeign('stop_words_good_id_foreign');
            $table->foreign('campaign_good_id')->on('campaign_goods')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        $output->writeln('users');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_account_id_foreign');
            $table->foreign('account_id')->on('accounts')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->dropForeign('campaign_goods_campaign_id_foreign');
            $table->foreign('campaign_id')->on('campaigns')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropForeign('campaign_keywords_campaign_good_id_foreign');
            $table->foreign('campaign_good_id')->on('campaign_goods')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropForeign('campaign_statistics_campaign_id_foreign');
            $table->foreign('campaign_id')->on('campaigns')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_account_id_foreign');
            $table->foreign('account_id')->on('accounts')->references('id')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
