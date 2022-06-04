<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbTemporaryProductsAndOzTemporaryProductsAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('oz_temporary_products', 'account_client_id')) {
            Schema::table('oz_temporary_products', function (Blueprint $table) {
                $table->string('account_client_id')->index()->after('account_id')->comment('Для хранения client id');
            });
        }
        if (!Schema::hasColumn('wb_temporary_products', 'account_client_id')) {
            Schema::table('wb_temporary_products', function (Blueprint $table) {
                $table->string('account_client_id')->index()->after('account_id')->comment('Для хранения client id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('wb_temporary_products', 'account_client_id')) {
            Schema::table('wb_temporary_products', function (Blueprint $table) {
                $table->dropColumn('account_client_id');
            });
        }
        if (Schema::hasColumn('wb_temporary_products', 'account_client_id')) {
            Schema::table('wb_temporary_products', function (Blueprint $table) {
                $table->dropColumn('account_client_id');
            });
        }
    }
}
