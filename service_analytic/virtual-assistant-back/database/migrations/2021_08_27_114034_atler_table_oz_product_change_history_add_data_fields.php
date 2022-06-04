<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AtlerTableOzProductChangeHistoryAddDataFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_product_change_history', function (Blueprint $table) {
            $table->json('response_data')->nullable()->after('is_send')->comment('Для хранения данных по ответу и Ozon');
            $table->json('request_data')->nullable()->after('is_send')->comment('Для хранения данных по запросу в Ozon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_product_change_history', function (Blueprint $table) {
            //
        });
    }
}
