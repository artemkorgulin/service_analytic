<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsAutoselectParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoselect_parameters', function (Blueprint $table) {
            $table->renameColumn('group_id', 'groupId');
            $table->renameColumn('campaign_good_id', 'campaignGoodId');
            $table->renameColumn('start_date', 'dateFrom');
            $table->renameColumn('end_date', 'dateTo');
            $table->renameColumn('category_id', 'categoryId');
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
            $table->renameColumn('groupId', 'group_id');
            $table->renameColumn('campaignGoodId', 'campaign_good_id');
            $table->renameColumn('dateFrom', 'start_date');
            $table->renameColumn('dateTo', 'end_date');
            $table->renameColumn('categoryId', 'category_id');
        });
    }
}
