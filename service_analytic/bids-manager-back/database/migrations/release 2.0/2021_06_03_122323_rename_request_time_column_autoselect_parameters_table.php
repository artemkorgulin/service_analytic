<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameRequestTimeColumnAutoselectParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoselect_parameters', function (Blueprint $table) {
            $table->renameColumn('request_time', 'requestTime');
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
            $table->renameColumn('requestTime', 'request_time');
        });
    }
}
