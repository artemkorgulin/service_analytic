<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableJobsNewAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('jobs_new_account')) {
            Schema::create('jobs_new_account', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->nullable(false)->index()->comment('Номер аккаунта');
                $table->string('platform')->nullable(false)->index()->comment('Платформа аккаунта');
                $table->timestamp('command_start_at')->index()->nullable()->comment('Во сколько запущена команда');
                $table->text('comment')->nullable()->comment('Комментарии');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs_new_account');
    }
}
