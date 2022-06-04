<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsAddFieldsRecomendationsAndRecomendedCharacteristics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->json('recommendations')->nullable()->after('nomenclatures')
                ->comment('Рекомендации по заполнению характеристик');
            $table->json('recommended_characteristics')->nullable()->after('recommendations')
                ->comment('Список всех характеристик обязательных для заполнения');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            //
        });
    }
}
