<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOzonSellerCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ozon_seller_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ozon_id')->index('ozon_seller_category_ozon_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 50)->nullable();
            $table->unsignedBigInteger('ozon_category_id')->nullable();

            $table->foreign('ozon_category_id', 'ozon_seller_categories_ozon_category_id_foreign')->references('id')->on('ozon_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('parent_id', 'ozon_seller_categories_parent_id_foreign')->references('id')->on('ozon_seller_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ozon_seller_categories');
    }
}
