<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzOptionStatItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_option_stat_items', function (Blueprint $table) {
            $table->decimal('average_price', 12,2)->nullable()->default(0)->comment('Средняя стоимость')->change();
            $table->integer('request_without_results')->nullable()->default(0)->comment('Запросы без результатов')->after('average_price');
            $table->decimal('share_of_request_without_results', 6,2)->nullable()->default(0)->comment('Доля запросов без результатов')->after('request_without_results');
            $table->decimal('share_of_request_with_same_results', 6,2)->nullable()->default(0)->comment('Доля запросов с похожими результатами')->after('share_of_request_without_results');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_option_stat_items', function (Blueprint $table) {
            //
        });
    }
}
