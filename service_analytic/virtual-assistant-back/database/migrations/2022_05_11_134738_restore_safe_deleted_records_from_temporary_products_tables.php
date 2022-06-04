<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestoreSafeDeletedRecordsFromTemporaryProductsTables extends Migration
{

    protected $tables = ['oz_temporary_products', 'wb_temporary_products'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            DB::table($table)->where('id', '>', 1)->update(['deleted_at' => null]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
