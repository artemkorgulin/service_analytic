<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteStopWordColumnFromCampaignGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_goods', function (Blueprint $table) {
//            $table->dropForeign('campaign_goods_stop_word_id_foreign');
//            $table->dropColumn('stop_word_id');
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
            $table->foreignId('stop_word_id')->nullable()->constrained('campaign_stop_words')
                                                    ->onUpdate('cascade')->onDelete('restrict');
        });
    }
}
