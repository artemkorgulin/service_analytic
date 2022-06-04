<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('table_model_id')->unsigned()->nullable()->comment('ограничения по привязкам ролей к другим моделям');
        });

        Schema::create('table_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model')->comment('название класса');
            $table->string('name')->comment('название нужно для вывода названия модели при формировании формы');
            $table->timestamps();
        });

        $last_insert_id = DB::table('table_models')->insertGetId(
            [
                'model' => 'App\Models\User',
                'name' => 'Модель пользователей',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);

        DB::table('table_models')->insert(
            [
                'model' => 'App\Models\Account',
                'name' => 'Модель аккаунтов',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);

        Schema::table('roles', function (Blueprint $table) {
            $table->foreign('table_model_id')->references('id')->on('table_models');
        });

        DB::table('roles')->where(['table_model_id' => null])->update(['table_model_id' => $last_insert_id]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_model_id');
        });
        Schema::dropIfExists('table_models');
    }
}
