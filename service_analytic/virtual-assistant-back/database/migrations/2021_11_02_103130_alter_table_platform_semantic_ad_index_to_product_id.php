<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePlatformSemanticAdIndexToProductId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('platform_semantics', function (Blueprint $table) {
            if (Schema::hasColumn('platform_semantics', 'product_id')) {
                $table->index('product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('platform_semantics', function (Blueprint $table) {
            if (Schema::hasColumn('platform_semantics', 'product_id')) {
                try {
                    $table->dropIndex('product_id');
                } catch (\Exception $e) {
                    print($e->getMessage());
                }
            }
        });
    }
}
