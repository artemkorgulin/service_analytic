<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesOzProductsAndOzTemporaryProductsSeparateSkuAndQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->renameColumn('sku', 'sku_fbo');
            $table->dropIndex('oz_products_sku_index');
            $table->renameColumn('quantity', 'quantity_fbo');
        });
        Schema::table('oz_products', function (Blueprint $table) {
            $table->string('sku_fbo')->nullable()->index()->comment('SKU FBO')->change();
            $table->string('sku_fbs')->nullable()->after('sku_fbo')->index()->comment('SKU FBS');
            $table->integer('quantity_fbo', false, true)->default(0)->comment('Наличие FBO')->change();
            $table->integer('quantity_fbs', false, true)->default(0)->comment('Наличие FBS')->after('quantity_fbo');
        });

        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->renameColumn('sku', 'sku_fbo');
            $table->renameColumn('quantity', 'quantity_fbo');
        });
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->string('sku_fbo')->nullable()->index()->comment('SKU FBO')->change();
            $table->string('sku_fbs')->nullable()->after('sku_fbo')->index()->comment('SKU FBS');
            $table->integer('quantity_fbo', false, true)->default(0)->comment('Наличие FBO')->change();
            $table->integer('quantity_fbs', false, true)->default(0)->comment('Наличие FBS')->after('quantity_fbo');
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
            $table->dropIndex('oz_products_sku_fbo_index');
            $table->dropIndex('oz_products_sku_fbs_index');
            $table->dropColumn('sku_fbs');
            $table->dropColumn('quantity_fbs');
            $table->renameColumn('sku_fbo', 'sku');
            $table->renameColumn('quantity_fbo', 'quantity');
            $table->index('sku');
        });

        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->dropIndex('oz_temporary_products_sku_fbo_index');
            $table->dropIndex('oz_temporary_products_sku_fbs_index');
            $table->dropColumn('sku_fbs');
            $table->dropColumn('quantity_fbs');
            $table->renameColumn('sku_fbo', 'sku');
            $table->renameColumn('quantity_fbo', 'quantity');
        });
    }
}
