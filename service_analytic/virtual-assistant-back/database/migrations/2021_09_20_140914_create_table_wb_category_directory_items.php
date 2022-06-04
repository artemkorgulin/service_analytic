<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWbCategoryDirectoryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_category_directory_items', function (Blueprint $table) {
            $table->unsignedBigInteger('wb_category_id')->nullable(false)->index()->comment('Категория Wilberrries (ссылка на код товара)');
            $table->unsignedBigInteger('wb_directory_item_id')->nullable(false)->index()->comment('Ссылку на id характеристики из справочника');

            $table->foreign('wb_category_id')->on('wb_categories')->references('id')->cascadeOnDelete();
            $table->foreign('wb_directory_item_id')->on('wb_directory_items')->references('id')->cascadeOnDelete();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_category_directory_items');
    }
}
