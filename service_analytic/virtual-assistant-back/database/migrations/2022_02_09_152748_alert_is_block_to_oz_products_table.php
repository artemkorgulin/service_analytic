<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertIsBlockToOzProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->boolean('is_block')
                ->default(false)
                ->index()
                ->comment('Защита от одновременного редактирования');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->dropColumn('is_block');
        });
    }
}
