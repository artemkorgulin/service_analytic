<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagsFieldsToV2ProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_products', function (Blueprint $table) {
            $table->boolean('card_updated')->default(true);
            $table->boolean('characteristics_updated')->default(true);
            $table->boolean('position_updated')->default(true);
            $table->dateTime('characteristics_updated_at')->nullable();
            $table->dateTime('mpstat_updated_at')->nullable();
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
            $table->dropColumn('card_updated');
            $table->dropColumn('characteristics_updated');
            $table->dropColumn('position_updated');
            $table->dropColumn('characteristics_updated_at');
            $table->dropColumn('mpstat_updated_at');
        });
    }
}
