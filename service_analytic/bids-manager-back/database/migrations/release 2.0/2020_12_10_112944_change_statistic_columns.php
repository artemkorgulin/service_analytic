<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatisticColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->renameColumn('consumption', 'cost');
            $table->renameColumn('views', 'shows');
            $table->renameColumn('revenue', 'profit');
            $table->renameColumn('avg_1000_views_price', 'avg_1000_shows_price');
            DB::statement('ALTER TABLE keyword_statistics CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE keyword_statistics CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
        });
        Schema::table('good_statistics', function(Blueprint $table) {
            $table->renameColumn('consumption', 'cost');
            $table->renameColumn('views', 'shows');
            $table->renameColumn('revenue', 'profit');
            $table->renameColumn('avg_1000_views_price', 'avg_1000_shows_price');
            DB::statement('ALTER TABLE good_statistics CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE good_statistics CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
            $table->foreign('good_id')->references('id')->on('good');
        });
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->renameColumn('consumption', 'cost');
            $table->renameColumn('views', 'shows');
            $table->renameColumn('revenue', 'profit');
            $table->renameColumn('avg_1000_views_price', 'avg_1000_shows_price');
            DB::statement('ALTER TABLE campaign_statistics CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE campaign_statistics CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->renameColumn('cost', 'consumption');
            $table->renameColumn('shows', 'views');
            $table->renameColumn('profit', 'revenue');
        });
        Schema::table('good_statistics', function(Blueprint $table) {
            $table->renameColumn('cost', 'consumption');
            $table->renameColumn('shows', 'views');
            $table->renameColumn('profit', 'revenue');
        });
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->renameColumn('cost', 'consumption');
            $table->renameColumn('shows', 'views');
            $table->renameColumn('profit', 'revenue');
        });
    }
}
