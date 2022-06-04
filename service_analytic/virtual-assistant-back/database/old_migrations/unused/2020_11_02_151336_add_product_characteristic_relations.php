<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductCharacteristicRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_characteristic_values', function (Blueprint $table)
        {
            $table->unsignedBigInteger('characteristic_name_id');

            $table->foreign('characteristic_name_id', 'cp_id_foreign')
                ->references('id')
                ->on('product_characteristic_names')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_characteristic_values', function (Blueprint $table)
        {
            $table->dropForeign('cp_id_foreign');
            $table->dropColumn('characteristic_name_id');

            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
}
