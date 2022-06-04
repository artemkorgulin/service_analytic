<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableVariantAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variant_attribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->index()->nullable(false)->comment('Ссылка на таблицу вариантов');
            $table->unsignedBigInteger('attribute_id')->index()->nullable(false)->comment('Ссылка на таблицу аттрибутов');

            $table->unique(['variant_id', 'attribute_id']);

            $table->foreign('variant_id')->on('variants')->references('id')->onDelete('cascade');
            $table->foreign('attribute_id')->on('attributes')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `variant_attribute` comment 'Связующая таблица между вариантами и их атрибутами'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_variant_attribute');
    }
}
