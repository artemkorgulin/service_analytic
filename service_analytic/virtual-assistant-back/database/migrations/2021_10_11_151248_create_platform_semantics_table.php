<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformSemanticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_semantics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->comment('id продукта');
            $table->unsignedBigInteger('category_id')->nullable()->comment('id категории из wb_categories , id категории из категорий Ozon');
            $table->enum('platform', ['WB', 'Ozon'])->default('WB')->comment('МаркетплейсWB, Ozon');
            $table->string('key_request')->nullable();
            $table->string('search_responce')->nullable();
            $table->string('name_ch')->nullable()->comment('Имя характеристики');
            $table->integer('popularity')->nullable()->comment('Частотность');
            $table->integer('conversion')->nullable()->comment('Конверсионность');
            $table->timestamps();
            $table->softDeletes();

//            $table->foreign('product_id')->on('oz_products')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platform_semantics');
    }
}
