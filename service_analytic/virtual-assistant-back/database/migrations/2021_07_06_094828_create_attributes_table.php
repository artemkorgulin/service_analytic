<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false)->index()->comment('Id пользователя');
            $table->unsignedBigInteger('account_id')->nullable(false)->index()->comment('Id аккаунта');
            $table->string('title')->index()->nullable(false)->comment('Наименование аттрибута');
            $table->string('code')->unique()->index()->nullable(false)->comment('Код аттрибута');
            $table->boolean('required')->default(false)->nullable()->comment('Необходимость аттрибута обязательный или нет');
            $table->boolean('multiple')->default(false)->nullable()->comment('Много ли значений');
            $table->boolean('added')->default(true)->nullable()->comment('Добавляемый или только значения из справочника');
            $table->boolean('facet')->default(false)->nullable()->comment('Участвует ли поле в поиске');
            $table->unsignedBigInteger('dictionary_id')->nullable(true)->comment('ID справочника');
            $table->text('comment')->nullable(true)->comment('Комментарии к аттрибуту');

            $table->timestamps();
        });

        \DB::statement("ALTER TABLE `attributes` comment 'Таблица аттрибутов товара (больше как справочник)'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attributes');
    }
}
