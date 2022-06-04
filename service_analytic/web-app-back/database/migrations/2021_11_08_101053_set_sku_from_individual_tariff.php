<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

class SetSkuFromIndividualTariff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tariffs')->where('name', '=', 'Индивидуальный')->update(['sku' => '99999999']);
        $userIds = DB::table('users')->select('id')->pluck('id')->toArray();

        foreach ($userIds as $id) {
            Cache::forget('proxy_user_tariffs_'.$id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tariffs')->where('name', '=', 'Индивидуальный')->update(['sku' => null]);
    }
}
