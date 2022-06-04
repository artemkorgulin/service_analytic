<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2WebCategoriesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_web_categories_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_category_id');
            $table->double('min_price', 8, 2);
            $table->double('max_price', 8, 2);
            $table->integer('min_reviews');
            $table->double('min_rating', 8, 2);
            $table->integer('min_photos');
            $table->double('average_price', 8, 2)->default(0.00);
            $table->integer('max_reviews')->default(false);
            $table->timestamps();

            $table->foreign('web_category_id', 'v2_web_categories_history_web_category_id_foreign')->references('id')->on('v2_web_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_web_categories_history');
    }
}
