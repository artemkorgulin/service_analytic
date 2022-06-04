<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWbFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('directory_id')->index()->commnent('Cсылка на словарь');
            $table->string('title')->index()->unique()->commnent('Название опции (группы опций)');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('directory_id')->on('wb_directories')
                ->references('id')->cascadeOnDelete();
        });

        Schema::create('wb_feature_directory_items', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id')->index()->commnent('Ссылка характеристику');
            $table->unsignedBigInteger('item_id')->index()->commnent('Ссылка на значение');

            $table->foreign('feature_id')->on('wb_features')
                ->references('id')->cascadeOnDelete();
            $table->foreign('item_id')->on('wb_directory_items')
                ->references('id')->cascadeOnDelete();
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_features');
    }
}
