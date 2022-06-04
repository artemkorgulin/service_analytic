<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2TriggerChangeMinPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_trigger_change_min_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_category_id')->constrained('v2_web_categories');
            $table->integer('min_photos');
            $table->foreignId('product_id')->constrained('v2_products');
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
        Schema::dropIfExists('v2_trigger_change_min_photos');
    }
}
