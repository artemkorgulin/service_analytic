<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbBarcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_barcodes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index()->comment('Код баркода');
            $table->boolean('used')->default(false)->comment('Использован баркод или нет');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `wb_barcodes` comment 'Таблица, которая содержит информацию по сгенерированным баркодам'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_barcodes');
    }
}
