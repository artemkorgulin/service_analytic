<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzTemporaryProductsAddExternalIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->integer('external_id')->index()->nullable()->default(0)->after('offer_id')->comment('ID продукта в Ozon');
            $table->string('image')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
