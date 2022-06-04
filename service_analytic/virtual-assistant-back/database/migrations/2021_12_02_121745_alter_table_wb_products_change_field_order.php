<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsChangeFieldOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            if (Schema::hasColumns('wb_products', ['visibility_optimization', 'content_optimization', 'search_optimization'])) {
                DB::statement("ALTER TABLE wb_products MODIFY COLUMN visibility_optimization INTEGER AFTER optimization");
                DB::statement("ALTER TABLE wb_products MODIFY COLUMN content_optimization INTEGER AFTER visibility_optimization");
                DB::statement("ALTER TABLE wb_products MODIFY COLUMN search_optimization INTEGER AFTER content_optimization");
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
        // Not make it down migration because change column order in not important
    }
}
