<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbDirectoryItemsAddStaticsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_directory_items', function (Blueprint $table) {
            $table->boolean('has_in_ozon')->nullable()->index()->after('translation')->comment('Есть ли тоже самое значение в Ozon');
            $table->integer('popularity')->nullable()->index()->after('translation')->comment('Популярность запроса (значения опции)');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_directory_items', function (Blueprint $table) {
            //
        });
    }
}
