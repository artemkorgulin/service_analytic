<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable()->index()->comment('ID пользователя');
            $table->bigInteger('account_id')->unsigned()->nullable(false)->index()->comment('')->comment('Аккаунт пользователя');
            $table->string('url')->index()->comment('URL изображения');
            $table->string('title')->index()->comment('Название изображения');
            $table->text('comment')->comment('Комментарий к изображению');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `images` comment 'Таблица для хранения изображений'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
