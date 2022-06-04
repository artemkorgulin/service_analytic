<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_params', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('platform_id')->nullable(false)->unsigned()->index();
            $table->bigInteger('account_id')->nullable(false)->unsigned()->index();
            $table->string('param')->nullable(false)->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('platform_id')->on('platforms')->references('id')->onDelete('cascade');
            $table->foreign('account_id')->on('accounts')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_params');
    }
}
