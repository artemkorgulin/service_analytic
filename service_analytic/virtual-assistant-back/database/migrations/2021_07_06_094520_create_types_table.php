<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index()->comment('ID родителя (категории товаров)');
            $table->string('title')->nullable(false)->index()->comment('Наименование категории товара');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки');
            $table->json('settings')->nullable()->comment('Хранение параметров (полей и т.д.)');
            $table->text('comment')->nullable()->comment('Комментарии к категориям для пользователя');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `types` comment 'Типы товаров (дерево или таксономия) нужна так как категории могут быть у всех продавцов разные'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('types');
    }
}
