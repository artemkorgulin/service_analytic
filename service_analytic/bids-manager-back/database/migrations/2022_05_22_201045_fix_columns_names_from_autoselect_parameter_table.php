<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixColumnsNamesFromAutoselectParameterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoselect_parameters', function (Blueprint $table) {
            $table->renameColumn('requestTime', 'request_time');
            $table->renameColumn('groupId', 'group_id');
            $table->renameColumn('campaignGoodId', 'campaign_product_id');
            $table->renameColumn('categoryId', 'category_id');
            $table->renameColumn('dateFrom', 'date_from');
            $table->renameColumn('dateTo', 'date_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autoselect_parameters', function (Blueprint $table) {
            $table->renameColumn('request_time', 'requestTime');
            $table->renameColumn('group_id', 'groupId');
            $table->renameColumn('campaign_product_id', 'campaignGoodId');
            $table->renameColumn('category_id', 'categoryId');
            $table->renameColumn('date_from', 'dateFrom');
            $table->renameColumn('date_to', 'dateTo');
        });
    }
}
