<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbDirectoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_directory_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wb_directory_id')->nullable(false)->index()->unsigned();
            $table->string('title')->nullable(false)->index();
            $table->string('translation')->nullable()->index();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['wb_directory_id', 'title']);
            $table->foreign('wb_directory_id')->on('wb_directories')->references('id')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_directory_items');
    }
}
