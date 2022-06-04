<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('web_category_id')->constrained('v2_web_categories');
            $table->float('min_price');
            $table->float('max_price');
            $table->integer('min_reviews');
            $table->float('min_rating');
            $table->integer('min_photos');
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
        Schema::dropIfExists('v2_web_categories_history');
    }
}
