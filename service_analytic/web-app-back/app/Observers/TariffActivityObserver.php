<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\TariffActivity;
use App\Services\NovaService;
use App\Services\UserService;

class TariffActivityObserver
{

    /**
     * Handle the TariffActivity "created" event.
     *
     * @param  \App\Models\TariffActivity  $tariffActivity
     *
     * @return void
     */
    public function created(TariffActivity $tariffActivity)
    {
        //assign order id to the freshly created tariff activity
        //check if there's already an order for the provided tariff and user
        $id = Order::select('id')
            ->whereJsonContains('tariff_ids', $tariffActivity->tariff_id)
            ->whereNotNull('captured_at')
            ->where('user_id', $tariffActivity->user_id)
            ->first()?->id;

        //create a new order if there's not
        if (!$id) {
            $novaService = new NovaService();
            $order = $novaService->createDefaultOrder($tariffActivity->tariff_id, $tariffActivity->user_id);
            $id = $order->id;
        }

        if ($id) {
            TariffActivity::where('id', $tariffActivity->id)->update(['order_id' => $id]);
        }

        $this->forgetCache($tariffActivity);
    }


    /**
     * Handle the TariffActivity "updated" event.
     *
     * @param  \App\Models\TariffActivity  $tariffActivity
     *
     * @return void
     */
    public function updated(TariffActivity $tariffActivity): void
    {
        $this->forgetCache($tariffActivity);
    }


    /**
     * Handle the TariffActivity "deleted" event.
     *
     * @param  \App\Models\TariffActivity  $tariffActivity
     *
     * @return void
     */
    public function deleted(TariffActivity $tariffActivity): void
    {
        $this->forgetCache($tariffActivity);
    }


    /**
     * @param  TariffActivity  $tariffActivity
     *
     * @return void
     */
    private function forgetCache(TariffActivity $tariffActivity): void
    {
        if ($tariffActivity->user) {
            (new UserService($tariffActivity->user))
                ->forgetTariffCache();
        }
    }
}
