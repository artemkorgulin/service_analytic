<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->string('name');
        });

        DB::table('notification_types')->insert([
            ['id' => 1, 'name' => 'Технические уведомления'],
            ['id' => 2, 'name' => 'Биллинг'],
            ['id' => 3, 'name' => 'Карточка товара'],
            ['id' => 4, 'name' => 'Оптимизация'],
            ['id' => 5, 'name' => 'Рекламные компании'],
            ['id' => 6, 'name' => 'Ценовой автомат'],
            ['id' => 7, 'name' => 'Личный кабинет'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_types');
    }
}
