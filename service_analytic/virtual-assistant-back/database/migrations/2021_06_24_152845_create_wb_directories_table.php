<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbDirectoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_directories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(false)->index();
            $table->string('slug')->nullable(false)->index();
            $table->bigInteger('qty')->nullable(false)->default(0);
            $table->text('comment')->nullable(true);
            $table->timestamps();

            $table->unique(['title', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_directories');
    }
}
