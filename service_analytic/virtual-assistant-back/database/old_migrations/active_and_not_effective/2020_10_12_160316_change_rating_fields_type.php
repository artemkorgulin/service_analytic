<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRatingFieldsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->decimal('rating', 10, 2, true)->change();
        });

        Schema::table('characteristics', function (Blueprint $table) {
            $table->decimal('rating', 10, 2, true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->integer('rating', false, true)->change();
        });

        Schema::table('characteristics', function (Blueprint $table) {
            $table->integer('rating', false, true)->change();
        });
    }
}
