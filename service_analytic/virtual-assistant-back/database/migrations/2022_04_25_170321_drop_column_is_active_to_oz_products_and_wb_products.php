<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnIsActiveToOzProductsAndWbProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('oz_products', 'is_active')) {
            Schema::table('oz_products', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasColumn('wb_products', 'is_active')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('oz_products', 'is_active')) {
            Schema::table('oz_products', function (Blueprint $table) {
                $table->boolean('is_active')->comment('Удаленные товары.');
            });
        }

        if (!Schema::hasColumn('wb_products', 'is_active')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->boolean('is_active')->comment('Удаленные товары.');
            });
        }
    }
}
