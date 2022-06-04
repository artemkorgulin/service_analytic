<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('product_id')->constrained('v2_products');
            $table->string('name');
            $table->foreignId('status_id')->constrained('v2_product_statuses');
            $table->bigInteger('task_id');
            $table->boolean('is_send')->default(0);
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
        Schema::dropIfExists('v2_product_change_history');
    }
}
