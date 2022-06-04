<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('menu_id')->index()->nullable(false);
            $table->bigInteger('parent_id')->index()->default(0)->nullable();
            $table->integer('position')->index()->default(0)->nullable();
            $table->string('name')->index()->nullable(false);
            $table->string('url')->index()->nullable(false);
            $table->json('banners');
            $table->text('description')->nullable();
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('wb_categories');
    }
}
