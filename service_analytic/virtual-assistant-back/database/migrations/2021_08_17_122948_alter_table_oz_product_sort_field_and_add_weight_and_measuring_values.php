<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzProductSortFieldAndAddWeightAndMeasuringValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {

            $table->string('dimension_unit', 10)->nullable()->default('sm')->comment('Единица размеров товара')->after('premium_price');
            $table->integer('depth')->nullable()->default(0)->comment('Глубина товара')->after('dimension_unit');
            $table->integer('height')->nullable()->default(0)->comment('Высота товара')->after('depth');
            $table->integer('width')->nullable()->default(0)->comment('Ширина товара')->after('height');

            $table->string('weight_unit', 10)->nullable()->default('kg')->comment('Единица веса товара')->after('width');
            $table->integer('weight')->nullable()->default(0)->comment('Вес товара')->after('weight_unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            if (Schema::hasColumn('oz_products','dimension_unit'))
                $table->dropColumn('dimension_unit');
            if (Schema::hasColumn('oz_products','depth'))
                $table->dropColumn('depth');
            if (Schema::hasColumn('oz_products','height'))
                $table->dropColumn('height');
            if (Schema::hasColumn('oz_products','width'))
                $table->dropColumn('width');
            if (Schema::hasColumn('oz_products','weight_unit'))
                $table->dropColumn('weight_unit');
            if (Schema::hasColumn('oz_products','weight'))
                $table->dropColumn('weight');
        });
    }
}
