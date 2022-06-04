<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCampaignGoodIdColumnInCampaignStopWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->renameColumn('campaign_good_id', 'campaign_product_id');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->renameColumn('campaign_good_id', 'campaign_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->renameColumn('campaign_product_id', 'campaign_good_id');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->renameColumn('campaign_product_id', 'campaign_good_id');
        });
    }
}
