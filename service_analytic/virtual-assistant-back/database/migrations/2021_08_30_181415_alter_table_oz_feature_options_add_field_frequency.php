<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzFeatureOptionsAddFieldFrequency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->integer('popularity')->nullable()->index()->after('external_id')->comment('Популярность запроса (значения опции)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_feature_options', function (Blueprint $table) {
            //
        });
    }
}
