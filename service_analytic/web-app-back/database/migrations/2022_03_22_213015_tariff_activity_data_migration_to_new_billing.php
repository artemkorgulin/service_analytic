<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TariffActivityDataMigrationToNewBilling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tariffActivities = DB::table('tariff_activity')
            ->select(
                'order_id',
                DB::raw('min(start_at) as start_at'),
                DB::raw('max(end_at) as end_at')
            )
            ->groupBy('order_id')
            ->get();

        $promoTariffId = DB::table('tariffs')->where('alias', 'promo')->value('id');
        $tariffServices = DB::table('service_tariff')->where('tariff_id', $promoTariffId)->get();

        foreach ($tariffActivities as $tariffActivity) {
            if (DB::table('orders')->where('id', $tariffActivity->order_id)->exists()) {
                DB::table('orders')
                    ->where('id', $tariffActivity->order_id)
                    ->update([
                        'start_at' => $tariffActivity->start_at,
                        'end_at' => $tariffActivity->end_at,
                        'tariff_id' => $promoTariffId
                    ]);
                if (!DB::table('order_service')->where('order_id', $tariffActivity->order_id)->exists()) {
                    foreach ($tariffServices as $tariffService) {
                        DB::table('order_service')->insert([
                            'order_id' => $tariffActivity->order_id,
                            'service_id' => $tariffService->service_id,
                            'ordered_amount' => $tariffService->amount,
                            'total_price' => 0,
                        ]);
                    }
                }
            }
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
