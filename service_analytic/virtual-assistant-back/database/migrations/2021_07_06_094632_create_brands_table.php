<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique()->index()->comment('Название бренда');
            $table->text('small_description')->nullable()->comment('Короткое описание');
            $table->text('description')->nullable()->comment('Длинное описание');
            $table->string('country_of_origin')->nullable(true)->comment('Основная страна штабквартира');
            $table->string('url')->comment('Url сайта бренда');
            $table->string('meta_title')->nullable()->comment('Мета название');
            $table->string('meta_keywords')->nullable()->comment('Мета key-words');
            $table->text('meta_description')->nullable()->comment('Мета название');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brands');
    }
}
