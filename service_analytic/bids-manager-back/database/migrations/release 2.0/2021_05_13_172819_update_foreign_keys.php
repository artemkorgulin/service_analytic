<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_goods', function (Blueprint $table) {
//            $table->dropIndex('idx_good_id');
//            $table->dropIndex('idx_good_campaign_id');
//            $table->dropIndex('good_id_index');

//            $table->dropForeign('good_campaign_id_foreign');
//            $table->foreign('campaign_id')->on('campaigns')->references('id')
//                  ->onUpdate('cascade')->onDelete('restrict');

//            $table->dropForeign('good_status_id_foreign');
            $table->foreign('status_id')->on('statuses')->references('id')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropIndex('campaign_statistics_id_index');
            $table->dropIndex('campaign_statistics_campaign_id_index');

            $table->foreign('campaign_id')->on('campaigns')->references('id')
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
        //
    }
}
