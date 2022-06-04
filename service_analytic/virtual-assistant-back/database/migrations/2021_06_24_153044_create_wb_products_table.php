<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index()->nullable(false)->unsigned();
            $table->string('card_id')->index()->nullable(false);
            $table->bigInteger('imt_id')->index()->nullable(false);
            $table->bigInteger('user_id')->index()->nullable();
            $table->string('supplier_id')->index()->nullable();
            $table->string('imt_supplier_id')->index()->nullable();
            $table->string('title')->index()->nullable();
            $table->string('object')->index();
            $table->string('parent')->index();
            $table->string('country_production')->index();
            $table->string('supplier_vendor_code')->index();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->on('accounts')->references('id')->onDelete('cascade');
            $table->unique(['account_id', 'card_id', 'imt_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_products');
    }
}
