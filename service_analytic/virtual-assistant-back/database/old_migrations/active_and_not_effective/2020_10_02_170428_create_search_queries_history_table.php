<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchQueriesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_query_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('search_query_id')
                  ->constrained('search_queries')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->integer('popularity', false, true);
            $table->integer('additions_to_cart', false, true);
            $table->integer('avg_price', false, true);
            $table->integer('products_count', false, true);
            $table->integer('rating', false, true);
            $table->date('parsing_date');
        });

        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropColumn('last_rate');
            $table->renameColumn('purchases', 'additions_to_cart');
            $table->renameColumn('result_count', 'products_count');
            $table->integer('rating', false, true)->after('result_count');
        });

        Schema::dropIfExists('ratings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_query_histories');

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('rate', false, true);
            $table->dateTime('date');
        });

        Schema::table('search_queries', function (Blueprint $table) {
            $table->smallInteger('last_rate', false, true);
            $table->renameColumn('additions_to_cart', 'purchases');
            $table->renameColumn('products_count', 'result_count');
            $table->dropColumn('rating');
        });
    }
}
