<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropOldTableAndColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('old_tariffs');
        Schema::dropIfExists('old_permissions');
        Schema::dropIfExists('payments');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ozon_client_id')) {
                $table->dropColumn('ozon_client_id');
            }

            if (Schema::hasColumn('users', 'ozon_client_api_key')) {
                $table->dropColumn('ozon_client_api_key');
            }

            if (Schema::hasColumn('users', 'ozon_supplier_id')) {
                $table->dropColumn('ozon_supplier_id');
            }

            if (Schema::hasColumn('users', 'ozon_supplier_api_key')) {
                $table->dropColumn('ozon_supplier_api_key');
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('old_tariffs', function (Blueprint $table) {
            //
        });
        Schema::create('old_permissions', function (Blueprint $table) {
            //
        });

        Schema::create('payments', function (Blueprint $table) {
            //
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('ozon_client_id')->nullable();
            $table->string('ozon_client_api_key')->nullable();
            $table->string('ozon_supplier_id')->nullable();
            $table->string('ozon_supplier_api_key')->nullable();
        });
    }
}
