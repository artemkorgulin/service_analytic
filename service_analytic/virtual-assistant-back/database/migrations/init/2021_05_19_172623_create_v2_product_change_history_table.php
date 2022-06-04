<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2ProductChangeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_change_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->bigInteger('task_id')->nullable();
            $table->boolean('is_send')->default(0);
            $table->timestamps();

            $table->foreign('product_id', 'v2_product_change_history_product_id_foreign')->references('id')->on('v2_products');
            $table->foreign('status_id', 'v2_product_change_history_status_id_foreign')->references('id')->on('v2_product_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_product_change_history');
    }
}
