<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzOptionStatSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_option_stat_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_stat_id')->nullable(false)->index()->comment('Поле для foreign key');
            $table->string('key_request')->nullable(false)->index()->comment('Ключевой запрос');
            $table->integer('summary_popularity')->nullable(false)->default(0)->comment('Суммарное поле по популярности запроса');
            $table->timestamps();

            $table->foreign('option_stat_id')->on('oz_option_stats')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_option_stat_summaries');
    }
}
