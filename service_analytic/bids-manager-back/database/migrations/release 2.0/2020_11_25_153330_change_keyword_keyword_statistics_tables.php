<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeKeywordKeywordStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyword', function (Blueprint $table) {
            $table->dropColumn('bet');
            $table->string('sku')->nullable();
            $table->dropForeign('keyword_good_id_foreign');
            $table->dropColumn('good_id');
        });

        Schema::table('keyword_statistics', function (Blueprint $table) {
            $table->integer('keyword_id')->nullable()->change();
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
