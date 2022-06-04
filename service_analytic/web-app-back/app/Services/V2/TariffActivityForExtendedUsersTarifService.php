<?php

namespace App\Services\V2;

use App\Models\Order;
use App\Models\OldTariff;
use App\Models\TariffActivity;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class TariffActivityService
 *
 * @package App\Services\V2
 */
class TariffActivityForExtendedUsersTarifService
{
    const WAIT = '0'; // Ожидает
    const ACTVE = '1'; // Активный
    const INACTVE = '2'; // Неактивный
    const PROMO_ORDER_ID = 1003;
    const INTERVAL = 1;

    /**
     * @var User
     */
    protected User $user;
    public TariffActivity $tariffActivity;

    /**
     * TariffActivityService constructor.
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return TariffActivity
     */
    protected function createTariffActivityForExtendedUserTariff(): TariffActivity
    {
        $startAt = Carbon::now();
        $this->tariffActivity = new TariffActivity();
        $this->tariffActivity->user_id = $this->user->id;
        $this->tariffActivity->order_id = $this->checkOrder();
        $this->tariffActivity->tariff_id = OldTariff::TARIFF_PROMO_ID;
        $this->tariffActivity->status = self::ACTVE;
        $this->tariffActivity->start_at = $startAt->format("Y-m-d H:i:s");
        $this->tariffActivity->end_at = $startAt->add(self::INTERVAL, 'month')->format("Y-m-d H:i:s");
        $this->tariffActivity->save();

        return $this->tariffActivity;
    }

    /**
     * Активация подписки
     *
     * @return TariffActivity
     */
    public function activateSubscription(): TariffActivity
    {
        $this->deactivateSubscription();

        return $this->createTariffActivityForExtendedUserTariff();
    }

    /**
     * Деактивация подписки
     * @return void
     */
    private function deactivateSubscription(): void
    {
        TariffActivity::where('user_id', $this->user->id)
            ->update(['status' => TariffActivity::INACTVE]);
    }

    /**
     * Проверка на существование ордера
     * @return int
     */
    private function checkOrder(): int
    {
        if(!Order::whereId(self::PROMO_ORDER_ID)->first()){
            return  Order::where('user_id', '=', env('TEST_USER'))->first()->id;
        }

        return self::PROMO_ORDER_ID;
    }
}
