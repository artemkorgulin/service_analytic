<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('name')->nullable();
            $table->unsignedBigInteger('external_id');
            $table->boolean('is_reference')->default(0);
            $table->integer('count_values')->default(0);
            $table->integer('old_count_values')->default(0);
            $table->boolean('is_specialty')->default(0);
            $table->boolean('is_collection')->default(0);
            $table->boolean('is_required')->default(0);
            $table->text('description')->nullable();
            $table->string('type')->nullable();
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
