<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsV2ProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->dropForeign('v2_products_status_id_foreign');
        });
        Schema::table('v2_products', function (Blueprint $table) {
            $table->string('photo_url')->change();
            $table->foreignId('status_id')->nullable(false)->change()->constrained('v2_product_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->dropForeign('v2_products_status_id_foreign');
        });
        Schema::table('v2_products', function (Blueprint $table) {
            $table->integer('photo_url')->change();
            $table->foreignId('status_id')->nullable(true)->change()->constrained('v2_product_statuses');
        });
    }
}
