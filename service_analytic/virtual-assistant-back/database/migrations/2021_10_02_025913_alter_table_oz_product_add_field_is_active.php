<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzProductAddFieldIsActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_products', 'is_active')) {
                $table->boolean('is_active')->nullable()->index()->after('deleted_at')->default(false)->comment('Активный ли продукт или нет ');
            }
        });

        Schema::table('wb_products', function (Blueprint $table) {
            if (!Schema::hasColumn('wb_products', 'is_active')) {
                $table->boolean('is_active')->nullable()->index()->after('is_advertised')->default(false)->comment('Активный ли продукт или нет ');
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
        Schema::table('oz_products', function (Blueprint $table) {
            if (Schema::hasColumn('oz_products', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('wb_products', function (Blueprint $table) {
            if (Schema::hasColumn('wb_products', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}
