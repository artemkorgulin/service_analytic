<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeysInWbNomenclaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_nomenclatures', function (Blueprint $table) {
            try {
                $table->dropForeign('wb_nomenclatures_account_id_foreign');
            } catch (\Exception $e) {
                print $e->getMessage();
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
        Schema::table('wb_nomenclatures', function (Blueprint $table) {
            //
        });
    }
}
