<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Schema::rename('keyword', 'good_keywords');

        Schema::create('keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('name');
            $table->date('date');
            $table->integer('popularity');
        });

        Schema::table('good_keywords', function (Blueprint $table) {
            $table->dropColumn('user_query');
            $table->foreignId('keyword_id')->constrained('keyword')->onUpdate('cascade')->onDelete('restrict');
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
