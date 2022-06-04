<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzProductImportInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_product_import_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false)->index();
            $table->unsignedBigInteger('account')->nullable(false)->index();
            $table->bigInteger('task_id')->index()->default(0)->nullable(false);
            $table->json('request')->nullable(true);
            $table->json('data')->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index();
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
        Schema::dropIfExists('oz_product_import_infos');
    }
}
