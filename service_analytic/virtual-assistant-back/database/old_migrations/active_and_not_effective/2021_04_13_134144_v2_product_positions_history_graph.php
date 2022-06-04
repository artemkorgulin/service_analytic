<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V2ProductPositionsHistoryGraph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_product_positions_history_graph', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('v2_products');
            $table->integer('position');
            $table->string('category')->nullable;
            $table->date('date');
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
        Schema::dropIfExists('v2_product_positions_history_graph');
    }
}
