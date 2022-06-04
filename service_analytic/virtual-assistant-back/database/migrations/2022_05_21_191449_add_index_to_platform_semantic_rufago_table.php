<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIndexToPlatformSemanticRufagoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->index([DB::raw('wb_product_name(25)')]);
            $table->index([DB::raw('wb_parent_name(25)')]);
            $table->index([DB::raw('oz_category_name(25)')]);
            $table->index([DB::raw('key_request(25)')]);
            $table->index([DB::raw('search_response(25)')]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->dropIndex(['wb_product_name(25)']);
            $table->dropIndex(['wb_parent_name(25)']);
            $table->dropIndex(['oz_category_name(25)']);
            $table->dropIndex(['key_request(25)']);
            $table->dropIndex(['search_response(25)']);
        });
    }
}
