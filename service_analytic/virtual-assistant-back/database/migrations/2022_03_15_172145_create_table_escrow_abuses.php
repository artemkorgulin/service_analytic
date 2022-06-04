<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEscrowAbuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escrow_abuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id', false, true)->index()->comment('Product ID');
            $table->string('site')->comment('Web-site URL');
            $table->string('self_product_link')->comment('Product URL');
            $table->string('another_product_link')->comment('Another product URL');
            $table->string('certificate_link')->comment('Certificate URL');
            $table->json('data')->comment('Data');
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
        Schema::dropIfExists('escrow_abuses');
    }
}
