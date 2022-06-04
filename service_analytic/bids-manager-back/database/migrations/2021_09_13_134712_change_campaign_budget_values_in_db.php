<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeCampaignBudgetValuesInDb extends Migration
{
    public const COEFFICIENT = 1000000;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('campaigns')->where('budget', '>', self::COEFFICIENT)
            ->update(['budget' => DB::raw('budget / ' . self::COEFFICIENT)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('campaigns')->where('budget', '<', self::COEFFICIENT)
            ->update(['budget' => DB::raw('budget * ' . self::COEFFICIENT)]);
    }
}
