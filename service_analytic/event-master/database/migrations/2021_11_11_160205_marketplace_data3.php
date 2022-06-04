<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketplaceData3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_subtypes')
            ->whereIn('id', [31, 32])
            ->update(['type_id' => 3]);

        DB::table('notification_subtypes')
            ->where('id', 31)
            ->update(['code' => 'card_product.marketplace_product_start_upload_success']);
        DB::table('notification_subtypes')
            ->where('id', 32)
            ->update(['code' => 'card_product.marketplace_product_start_upload_fail']);

        DB::table('notification_subtypes')
            ->whereIn('id', [28, 29, 30])
            ->update(['type_id' => 7]);

        DB::table('notification_schemas')
            ->where('type_id', 8)
            ->delete();

        DB::table('notifications')->where('type_id', 8)->delete();

        DB::table('notification_types')
            ->where('id', 8)
            ->delete();

        $users = DB::connection('wab')->table('users')->get();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->id,
                'type_id' => 3,
                'way_code' => 'email',
            ];
        }
        DB::table('notification_schemas')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_types')->insert([
            ['id' => 8, 'name' => 'Личный кабинет'],
        ]);

        DB::table('notification_subtypes')
            ->whereIn('id', [31, 32])
            ->update(['type_id' => 8]);

        DB::table('notification_subtypes')
            ->where('id', 31)
            ->update(['code' => 'marketplace.marketplace_product_start_upload_success']);
        DB::table('notification_subtypes')
            ->where('id', 32)
            ->update(['code' => 'marketplace.marketplace_product_start_upload_fail']);

        DB::table('notification_subtypes')
            ->whereIn('id', [28, 29, 30])
            ->update(['type_id' => 8]);

        DB::table('notifications')->where('type_id', 3)->delete();
    }
}
