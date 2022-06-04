<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbTemporaryProductAddFieldQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasColumn('wb_temporary_products', 'quantity')) {
            Schema::table('wb_temporary_products', function (Blueprint $table) {
                $table->integer('quantity')->index()->default(0)
                    ->comment('Храним остатки этом поле')->after('url');
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
        if (Schema::hasColumn('wb_temporary_products', 'quantity')) {
            Schema::table('wb_temporary_products', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
}
