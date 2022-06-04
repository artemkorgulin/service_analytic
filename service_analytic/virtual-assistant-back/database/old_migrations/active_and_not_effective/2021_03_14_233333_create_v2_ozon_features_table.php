<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2OzonFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_ozon_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('external_id')->unique();
            $table->foreignId('category_id')->constrained('v2_ozon_categories');
            $table->boolean('is_reference')->default(0);
            $table->integer('count_values')->default(0);
            $table->integer('old_count_values')->default(0);
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
        Schema::dropIfExists('v2_ozon_features');
    }
}
