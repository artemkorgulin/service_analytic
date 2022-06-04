<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('product_id')->constrained('v2_products');
            $table->boolean('is_shown')->default(0);
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
        Schema::dropIfExists('v2_trigger_remove_from_sale');
    }
}
