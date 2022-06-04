<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable()->index()->comment('ID пользователя');
            $table->bigInteger('account_id')->unsigned()->nullable(false)->index()->comment('')->comment('Аккаунт пользователя');
            $table->string('url')->index()->comment('URL файла');
            $table->string('mime_type')->index()->comment('Mime тип файла');
            $table->string('title')->index()->comment('Название файла');
            $table->text('comment')->comment('Комментарий к файлу');
            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `files` comment 'Таблица для хранения файлов'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
