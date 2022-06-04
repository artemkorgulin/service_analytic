<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesChangeBarcodeFieldFormatToStringFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_barcodes', function (Blueprint $table) {
            $table->string('barcode')->change();
        });

        Schema::table('wb_barcode_stocks', function (Blueprint $table) {
            $table->string('barcode')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_barcodes', function (Blueprint $table) {
            $table->bigInteger('barcode')->change();
        });

        Schema::table('wb_barcode_stocks', function (Blueprint $table) {
            $table->bigInteger('barcode')->change();
        });
    }
}
