<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Проще будет удалить, чем что-то в ней
        Schema::dropIfExists('wb_categories');

        Schema::create('wb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->nullable(false)->unique()->comment('Название типа продукта');
            $table->string('parent')->index()->nullable(true)->comment('Родительская группа продукта');
            $table->json('data')->nullable()->comment('Для хранения данных');
            $table->text('comment')->nullable()->comment('Комментарии');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `wb_categories` comment 'Таблица содержащая типы продуктов и их характеристики'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropDatabaseIfExists('wb_categories');
    }
}
