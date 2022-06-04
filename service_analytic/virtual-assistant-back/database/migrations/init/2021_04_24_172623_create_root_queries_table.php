<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRootQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('root_queries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable()->index('root_queries_name_index');
            $table->unsignedBigInteger('ozon_category_id');
            $table->boolean('is_visible')->default(0);
            $table->index(['ozon_category_id', 'name'], 'root_query_in_category_index');
            $table->index(['name', 'ozon_category_id'], 'root_queries_name_ozon_category_id_index');
            $table->timestamps();

            $table->foreign('ozon_category_id', 'root_queries_ozon_category_id_foreign')->references('id')->on('ozon_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('root_queries');
    }
}
