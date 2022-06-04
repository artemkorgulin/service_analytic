<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStrategyKeywordStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->decimal('avg_shows')->nullable()->unsigned()->comment('Средние показы за последние 7 дней');
            $table->decimal('avg_popularity')->nullable()->unsigned()->comment('Средняя популярность за последние 7 дней');
            $table->decimal('threshold_popularity')->nullable()->unsigned()->comment('Пороговое значение популярности для принятия решения');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->dropColumn('avg_shows');
            $table->dropColumn('avg_popularity');
            $table->dropColumn('threshold_popularity');
        });
    }
}
