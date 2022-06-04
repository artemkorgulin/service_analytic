<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsCampaignTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_types', function (Blueprint $table) {
            $table->renameColumn('name', 'code');
            $table->renameColumn('description', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_types', function (Blueprint $table) {
            $table->renameColumn('name', 'description');
            $table->renameColumn('code', 'name');
        });
    }
}
