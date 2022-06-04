<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzProductsChangeOptimizationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->integer('optimization')->nullable()->default(null)->change();
            $table->integer('content_optimization')->nullable()->default(null)->change();
            $table->integer('search_optimization')->nullable()->default(null)->change();
            $table->integer('visibility_optimization')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->integer('optimization')->default(0)->change();
            $table->integer('content_optimization')->default(0)->change();
            $table->integer('search_optimization')->default(0)->change();
            $table->integer('visibility_optimization')->default(0)->change();
        });
    }
}
