<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexKeywordIdToKeywordPopularitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyword_popularities', function (Blueprint $table) {
            $table->index(['keyword_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keyword_popularities', function (Blueprint $table) {
            $table->dropIndex('keyword_popularities_keyword_id_index');
        });
    }
}
