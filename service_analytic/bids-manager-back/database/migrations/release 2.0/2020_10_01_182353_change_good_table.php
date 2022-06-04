<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGoodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good', function (Blueprint $table) {
//            $table->dropForeign(['keyword_id']);
//            $table->dropForeign(['statistics_id']);
//            $table->dropColumn('keyword_id');
//            $table->dropColumn('statistics_id');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('campaign_id');
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
