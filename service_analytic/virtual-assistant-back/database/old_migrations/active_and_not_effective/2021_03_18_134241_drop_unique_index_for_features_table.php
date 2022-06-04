<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueIndexForFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->dropUnique('v2_ozon_features_external_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->unique('external_id');
        });
    }
}
