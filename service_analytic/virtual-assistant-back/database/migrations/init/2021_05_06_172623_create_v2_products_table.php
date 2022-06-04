<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('external_id');
            $table->string('url')->nullable();
            $table->string('sku')->nullable();
            $table->string('offer_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->double('price', 8, 2);
            $table->integer('count_photos')->default(0);
            $table->double('rating', 8, 2)->default(0.00);
            $table->integer('count_reviews')->default(0);
            $table->unsignedBigInteger('web_category_id')->nullable();
            $table->string('photo_url')->nullable();
            $table->double('recommended_price', 8, 2)->nullable();
            $table->boolean('is_for_sale')->default(0);
            $table->unsignedBigInteger('status_id');
            $table->boolean('is_advertised')->default(0);
            $table->boolean('card_updated')->default(1);
            $table->boolean('characteristics_updated')->default(1);
            $table->boolean('position_updated')->default(1);
            $table->dateTime('characteristics_updated_at')->nullable();
            $table->dateTime('mpstat_updated_at')->nullable();
            $table->boolean('show_success_alert')->default(0);
            $table->decimal('old_price', 8, 2);
            $table->boolean('is_test')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id', 'v2_products_category_id_foreign')->references('id')->on('v2_ozon_categories');
            $table->foreign('status_id', 'v2_products_status_id_foreign')->references('id')->on('v2_product_statuses');
            $table->foreign('user_id', 'v2_products_user_id_foreign')->references('id')->on('users');
            $table->foreign('web_category_id', 'v2_products_web_category_id_foreign')->references('id')->on('v2_web_categories');
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
