<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_file', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable(false)->comment('Ссылка на продукт');
            $table->unsignedBigInteger('file_id')->nullable(false)->comment('Ссылка на изображение');
            $table->string('title')->nullable()->comment('Наименование для файла');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки ');

            $table->foreign('product_id')->on('products')->references('id')->onDelete('cascade');
            $table->foreign('file_id')->on('files')->references('id')->onDelete('cascade');
        });

        \DB::statement("ALTER TABLE `product_file` comment 'Связующая таблица между товарами и файлами'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_file');
    }
}
