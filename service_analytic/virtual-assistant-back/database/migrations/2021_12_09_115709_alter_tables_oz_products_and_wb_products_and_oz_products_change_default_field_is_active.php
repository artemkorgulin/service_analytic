<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesOzProductsAndWbProductsAndOzProductsChangeDefaultFieldIsActive extends Migration
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
                $table->boolean('is_active')->default(true)->change();
            });
        }

        if (Schema::hasColumn('wb_products', 'is_active')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->change();
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
        if (Schema::hasColumn('oz_products', 'is_active')) {
            Schema::table('oz_products', function (Blueprint $table) {
                $table->boolean('is_active')->default(false)->change();
            });
        }

        if (Schema::hasColumn('wb_products', 'is_active')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->boolean('is_active')->default(false)->change();
            });
        }
    }
}
