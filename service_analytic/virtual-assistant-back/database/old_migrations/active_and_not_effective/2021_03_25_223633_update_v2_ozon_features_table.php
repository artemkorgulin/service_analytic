<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateV2OzonFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_ozon_features', function (Blueprint $table) {
            $table->boolean('is_specialty')->default(0);
            $table->boolean('is_collection')->default(0);
            $table->boolean('is_required')->default(0);
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
            $table->dropColumn('is_specialty');
            $table->dropColumn('is_collection');
            $table->dropColumn('is_required');
        });
    }
}
