<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('external_id');
            $table->string('url');
            $table->string('sku');
            $table->string('offer_id')->nullable();
            $table->string('name');
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->float('price');
            $table->integer('count_photos')->default(0);
            $table->float('rating')->default(0);
            $table->integer('count_reviews')->default(0);
            $table->foreignId('web_category_id')->constrained('v2_web_categories');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_products');
    }
}
