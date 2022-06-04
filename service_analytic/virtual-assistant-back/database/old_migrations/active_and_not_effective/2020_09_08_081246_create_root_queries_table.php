<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('name', 50);
            $table->foreignId('ozon_category_id')->constrained('ozon_categories');
            $table->foreignId('alias_id')->constrained('aliases');
            $table->string('brand', 50);
            $table->foreignId('search_query_id')->constrained('search_queries');
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
