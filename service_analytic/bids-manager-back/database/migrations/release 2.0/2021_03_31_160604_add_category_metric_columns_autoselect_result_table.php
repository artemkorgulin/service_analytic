<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryMetricColumnsAutoselectResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoselect_results', function (Blueprint $table) {
            $table->addColumn('float', 'category_popularity')->default(0.0);
            $table->addColumn('float', 'category_cart_add_count')->default(0.0);
            $table->addColumn('float', 'category_avg_cost')->default(0.0);
            $table->addColumn('float', 'category_crtc')->default(0.0);
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
            $table->removeColumn('category_popularity');
            $table->removeColumn('category_cart_add_count');
            $table->removeColumn('category_avg_cost');
            $table->removeColumn('category_crtc');
        });
    }
}
