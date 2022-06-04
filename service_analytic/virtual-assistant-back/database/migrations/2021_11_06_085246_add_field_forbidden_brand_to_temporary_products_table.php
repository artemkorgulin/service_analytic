<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldForbiddenBrandToTemporaryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_temporary_products', 'forbidden_brand_id')) {
                $table->unsignedBigInteger('forbidden_brand_id')->index()->nullable()->after('brand')->comment('Нужны ли права на бренд или нет id бренда');
            }
        });

        Schema::table('wb_temporary_products', function (Blueprint $table) {
            if (!Schema::hasColumn('wb_temporary_products', 'forbidden_brand_id')) {
                $table->unsignedBigInteger('forbidden_brand_id')->index()->nullable()->after('brand')->comment('Нужны ли права на бренд или нет id бренда');
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
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            if (Schema::hasColumn('oz_temporary_products', 'forbidden_brand')) {
                $table->dropColumn('forbidden_brand_id');
            }
        });

        Schema::table('wb_temporary_products', function (Blueprint $table) {
            if (Schema::hasColumn('wb_temporary_products', 'forbidden_brand')) {
                $table->dropColumn('forbidden_brand_id');
            }
        });
    }
}
