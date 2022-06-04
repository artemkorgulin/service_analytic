<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbBarcodeStocksRemoveIndexBarcodesAndAddIndexBarcodesCheckDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_barcode_stocks', function (Blueprint $table) {
            $table->dropUnique('wb_barcode_stocks_barcode_unique');
            $table->unique(['barcode', 'check_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_barcode_stocks', function (Blueprint $table) {
            $table->dropUnique('wb_barcode_stocks_barcode_check_date_unique');
            $table->unique('barcode');
        });
    }
}
