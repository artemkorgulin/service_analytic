<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OzProductsQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->integer('quantity')->nullable();
        });
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->integer('quantity')->nullable();
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
            $table->dropColumn('quantity');
        });
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
