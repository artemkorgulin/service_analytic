<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignIdCampaignKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->foreignId('campaign_good_id')->change();
            $table->foreign('campaign_good_id')->references('id')->on('campaign_goods')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropForeign('campaigns_keywords_campaign_good_id_foreign');
        });
    }
}
