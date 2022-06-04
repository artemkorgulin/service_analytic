<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateV2TriggerRemoveFromSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_trigger_remove_from_sale', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_shown')->default(0);
            $table->timestamps();

            $table->foreign('product_id', 'v2_trigger_remove_from_sale_product_id_foreign')->references('id')->on('v2_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_trigger_remove_from_sale');
    }
}
