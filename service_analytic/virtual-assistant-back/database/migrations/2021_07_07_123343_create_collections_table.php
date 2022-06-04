`<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable()->index()->comment('ID пользователя');
            $table->bigInteger('account_id')->unsigned()->nullable(false)->index()->comment('')->comment('Аккаунт пользователя');
            $table->string('title')->nullable(false)->index()->comment('Наименование коллекции товара');
            $table->integer('sorting')->default(0)->nullable()->index()->comment('Поле для сортировки ');
            $table->text('comment')->nullable()->comment('Комментарии к коллекций для пользователя');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `collections` comment 'Коллекции товара для каждого пользователя и аккаунта - своя'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collections');
    }
}
