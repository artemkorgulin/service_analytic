<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryRecommendations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_recommendations', function (Blueprint $table) {
            $table->id();
            $table->integer('web_id', 0, 1);
            $table->integer('quantity_photos_min', 0, 1)->nullable();
            $table->integer('quantity_comments_min', 0, 1)->nullable();
            $table->integer('price_min', 0, 1)->nullable();
            $table->integer('price_max', 0, 1)->nullable();
            $table->integer('price_avg', 0, 1)->nullable();
            $table->integer('sale_min', 0, 1)->nullable();
            $table->integer('sale_max', 0, 1)->nullable();
            $table->integer('sale_avg', 0, 1)->nullable();
            $table->integer('rating_min', 0, 1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_recommendations');
    }
}
