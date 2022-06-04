<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateV2ProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->integer('photo_url')->nullable(true);
            $table->float('recommended_price')->nullable(true);
            $table->boolean('is_for_sale')->default(0);
            $table->foreignId('status_id')->nullable(true)->constrained('v2_product_statuses');
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
            $table->dropColumn('photo_url');
            $table->dropColumn('recommended_price');
            $table->dropColumn('is_for_sale');
            $table->dropForeign('v2_products_status_id_foreign');
            $table->dropColumn('status_id');
        });
    }
}
