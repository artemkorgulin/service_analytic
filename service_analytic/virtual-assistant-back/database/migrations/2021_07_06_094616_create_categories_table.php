<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable()->index()->comment('ID пользователя');
            $table->bigInteger('account_id')->unsigned()->nullable(false)->index()->comment('')->comment('Аккаунт пользователя');
            $table->bigInteger('parent_id')->unsigned()->nullable()->index()->comment('ID родителя (категории товаров)');
            $table->string('title')->nullable(false)->index()->comment('Наименование категории товара');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки ');
            $table->text('comment')->nullable()->comment('Комментарии к категориям для пользователя');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `categories` comment 'Категории товаров (дерево) для каждого пользователя и аккаунта - своя'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
