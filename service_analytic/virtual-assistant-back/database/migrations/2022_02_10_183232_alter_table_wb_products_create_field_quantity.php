<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsCreateFieldQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('wb_products', 'quantity')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->integer('quantity')->index()->default(0)
                    ->comment('Храним остатки в нём')->after('optimization');
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
        if (Schema::hasColumn('wb_products', 'quantity')) {
            Schema::table('wb_products', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
}
