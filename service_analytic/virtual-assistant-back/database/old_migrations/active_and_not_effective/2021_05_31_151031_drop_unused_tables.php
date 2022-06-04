<?php

use App\Models\Characteristic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('characteristic_root_query_search_query')->truncate();
        Characteristic::query()->delete();

        Schema::dropIfExists('optimization_requests');
        Schema::dropIfExists('products_product_sets');
        Schema::dropIfExists('product_sets');
        Schema::dropIfExists('product_characteristic_values');
        Schema::dropIfExists('product_characteristics');
        Schema::dropIfExists('product_characteristics_product_characteristic_names');
        Schema::dropIfExists('product_characteristic_names');
        Schema::dropIfExists('product_search_query');
        Schema::dropIfExists('products');
        Schema::dropIfExists('search_query_ranks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('optimization_requests', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('ozon_category_id')->constrained('ozon_categories');
            $table->foreignId('root_query_id')->constrained('root_queries');
            $table->string('url', 255);
            $table->string('sku', 255);
            $table->string('brand', 50);
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('sku')->unique('sku');
            $table->string('category1', 128)->default('');
            $table->string('category2', 128)->default('');
            $table->string('category3', 128)->default('');
            $table->string('category4', 128)->default('');
            $table->string('name', 255);
            $table->string('brand', 255);
            $table->enum('shipping', ['FBO', 'FBS']);
            $table->unsignedInteger('price');
            $table->boolean('discounted');
            $table->unsignedInteger('review_count');
            $table->unsignedDecimal('rating', 2, 1);
            $table->boolean('rich_content');
            $table->string('seller', 255);
            $table->unsignedMediumInteger('desc_len');
        });

        Schema::create('product_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
        });

        Schema::create('products_product_sets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('product_set_id')
                  ->constrained('product_sets')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('product_characteristics', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('rating');
        });

        Schema::create('product_characteristics_product_characteristic_names', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('product_characteristic_names', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
        });

        Schema::create('product_characteristic_values', function (Blueprint $table) {
            $table->id();
            $table->string('value', 255);

            $table->unsignedBigInteger('characteristic_name_id');

            $table->foreign('characteristic_name_id', 'cp_id_foreign')
                  ->references('id')
                  ->on('product_characteristic_names')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('product_search_query', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('search_query_ranks', function (Blueprint $table)
        {
            $table->id();
            $table->timestamps();
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->decimal('filtering_ratio')->unsigned()->nullable();
            $table->date('date');
        });
    }
}
