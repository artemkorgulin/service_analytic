<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypesAutoselectResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoselect_results', function (Blueprint $table) {
            $table->float('popularity')->change();
            $table->float('cart_add_count')->change();
            $table->float('avg_cost')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autoselect_results', function (Blueprint $table) {
            $table->integer('popularity')->default(0.0)->change();
            $table->integer('cart_add_count')->default(0.0)->change();
            $table->integer('avg_cost')->default(0.0)->change();
        });
    }
}
