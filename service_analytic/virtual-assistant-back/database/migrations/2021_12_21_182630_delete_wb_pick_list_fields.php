<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteWbPickListFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_pick_lists', function (Blueprint $table) {
            $table->dropColumn('conv');
            $table->dropColumn('section');
        });
        Schema::table('wb_using_keywords', function (Blueprint $table) {
            $table->dropColumn('conv');
            $table->dropColumn('section');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_pick_lists', function (Blueprint $table) {
            $table->integer('conv');
            $table->integer('section');
        });
        Schema::table('wb_using_keywords', function (Blueprint $table) {
            $table->integer('conv');
            $table->integer('section');
        });
    }
}
