<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('ozon_id')->unsigned()->index('ozon_seller_category_ozon_id');
            $table->foreignId('parent_id')->nullable()->constrained('ozon_seller_categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 50);
            $table->foreignId('ozon_category_id')->nullable()->constrained('ozon_categories')->onUpdate('cascade')->onDelete('cascade');
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
