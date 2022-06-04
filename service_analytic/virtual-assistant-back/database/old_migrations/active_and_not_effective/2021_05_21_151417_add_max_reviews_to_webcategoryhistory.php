<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxReviewsToWebCategoryHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_web_categories_history', function (Blueprint $table) {
            $table->integer('max_reviews')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_web_categories_history', function (Blueprint $table) {
            $table->dropColumn('max_reviews');
        });
    }
}
