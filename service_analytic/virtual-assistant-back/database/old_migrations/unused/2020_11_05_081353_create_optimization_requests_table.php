<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptimizationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optimization_requests', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('ozon_category_id')->constrained('ozon_categories');
            $table->foreignId('root_query_id')->constrained('root_queries');
            $table->string('url', 255);
            $table->string('sku', 255);
            $table->string('brand', 50);
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('optimization_requests');
    }
}
