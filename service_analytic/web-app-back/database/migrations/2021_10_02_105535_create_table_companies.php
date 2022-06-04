<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('название организации');
            $table->string('inn')->nullable()->comment('Идентификационный номер налогоплательщика');
            $table->string('kpp')->nullable()->comment('Код причины постановки на учет');
            $table->string('ogrn')->nullable()->comment('Основной государственный регистрационный номер');
            $table->string('address')->nullable()->comment('адрес огрнанизации');
            $table->timestamps();

            $table->unique(['inn', 'kpp', 'ogrn']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
