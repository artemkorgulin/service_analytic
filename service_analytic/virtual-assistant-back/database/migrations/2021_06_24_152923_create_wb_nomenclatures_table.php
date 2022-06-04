<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbNomenclaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_nomenclatures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index()->nullable(false)->unsigned();
            $table->bigInteger('nm_id')->index()->nullable(false);
            $table->double('price',10, 2)->index();
            $table->double('discount',5, 2)->index();
            $table->string('promocode')->index();
            $table->timestamps();

            $table->foreign('account_id')->on('accounts')->references('id')->onDelete('cascade');
            $table->unique(['account_id', 'nm_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_nomenclatures');
    }
}
