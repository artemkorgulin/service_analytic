<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropTablesIfExistUsersAccountsPlaformsAccountParams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        print("Удаляем все старые таблицы\n");
        Schema::dropIfExists('account_params');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('users');
        if (Schema::hasTable('types')) {
            Schema::rename('types', 'product_types');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
