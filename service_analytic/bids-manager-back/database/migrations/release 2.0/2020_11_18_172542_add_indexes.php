<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->index('id');
            $table->index('campaign_id');
            $table->index('consumption');
            $table->index('views');
            $table->index('clicks');
            $table->index('ctr');
            $table->index('avg_1000_views_price');
            $table->index('avg_click_price');
        });

        Schema::table('campaign', function(Blueprint $table) {
            $table->index('id');
        });

        Schema::table('good', function(Blueprint $table) {
            $table->index('id');
            $table->index('campaign_id');
            $table->index('sku');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->dropIndex('id');
            $table->dropIndex('campaign_id');
            $table->dropIndex('consumption');
            $table->dropIndex('views');
            $table->dropIndex('clicks');
            $table->dropIndex('ctr');
            $table->dropIndex('avg_1000_views_price');
            $table->dropIndex('avg_click_price');
        });

        Schema::table('campaign', function(Blueprint $table) {
            $table->dropIndex('id');
        });

        Schema::table('good', function(Blueprint $table) {
            $table->dropIndex('id');
            $table->dropIndex('campaign_id');
            $table->dropIndex('sku');
            $table->dropIndex('price');
        });
    }
}
