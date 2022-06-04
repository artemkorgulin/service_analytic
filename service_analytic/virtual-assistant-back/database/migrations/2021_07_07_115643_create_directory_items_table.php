<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('directory_id')->unsigned()->nullable(false)->index()->comment('Ссылка на справочник');
            $table->string('value')->nullable(false)->comment('Значение справочника');
            $table->timestamps();

            $table->foreign('directory_id')->on('directories')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `directory_items` comment 'Таблица значений справочников'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('directory_items');
    }
}
