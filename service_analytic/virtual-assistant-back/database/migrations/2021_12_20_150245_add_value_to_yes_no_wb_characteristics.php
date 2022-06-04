<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddValueToYesNoWbCharacteristics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('yes_no_wb_characteristics')->insert([
            'category' => '',
            'characteristic' => 'Прямые поставки от производителя'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('yes_no_wb_characteristics')
            ->where('category', '')
            ->where('characteristic', 'Прямые поставки от производителя')
            ->delete();
    }
}
