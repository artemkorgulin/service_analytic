<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformSemanticRufagoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_semantic_rufago', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wb_product_id')->comment('id  из таблицы rufago');
            $table->string('WB_product_name')->default('');
            $table->bigInteger('wb_parent_id')->nullable();
            $table->string('wb_parent_name')->default('');
            $table->bigInteger('oz_category_id')->nullable();
            $table->string('oz_category_name')->default('');
            $table->string('key_request')->nullable();
            $table->string('search_response')->nullable();
            $table->integer('popularity')->nullable()->comment('Частотность');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platform_semantics_expands');
    }
}
