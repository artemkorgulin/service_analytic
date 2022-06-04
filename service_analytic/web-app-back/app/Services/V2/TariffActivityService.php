<?php
namespace App\Services\V2;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

use App\Services\Billing\OrderService;
use App\Models\Order;
use App\Models\TariffActivity;

/**
 * Class TariffActivityService
 * Сервис для работы с TariffActivity
 *
 * @package App\Services\V2
 */
class TariffActivityService
{
    const WAIT = '0'; // Ожидает
    const ACTVE = '1'; // Активный
    const INACTVE = '2'; // Неактивный

    /**
     * @var Order
     */
    protected $order;
    public $tariffActivity;

    /**
     * TariffActivityService constructor.
     *
     * @param  Collection  $tariffActivitys
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Collection
     */
    private function createTariffActivity(): Collection
    {
        $tariffs = json_decode($this->order->tariff_ids);
        foreach ($tariffs as $tariff) {
            $startAt = Carbon::now();
            $interval = 1;
            for ($i = 1; $i <= $this->order->period; $i++) {
                $this->tariffActivity = new TariffActivity();
                $this->tariffActivity->user_id = $this->order->user_id;
                $this->tariffActivity->order_id = $this->order->id;
                $this->tariffActivity->tariff_id = $tariff;
                $this->tariffActivity->start_at = $startAt->format("Y-m-d H:i:s");
                $this->tariffActivity->end_at = $startAt->add($interval, 'month')->format("Y-m-d H:i:s");
                $this->tariffActivity->save();
            }
        }
        return TariffActivity::where('order_id', $this->order->id)->groupBy('tariff_id')->get();
    }

    /**
     * Активация подписки (покупка тарифа)
     *
     * @return array
     */
    public function activateSubscription(): array
    {
        if ($this->isOldOrder()) {
            // Деактивация  тарифов после оплаты нового
            self::deactivateSubscription();

            // Делаем запись в таблицу tariff_activity
            $tariffActivitys = self::createTariffActivity();

            return self::activateTtariffActivity($tariffActivitys);
        } else {
            (new OrderService)->activateOrder($this->order);
            $this->order->save();
        }
        return [];
    }

    /**
     * @param  Collection  $tariffActivitys
     * @return array
     */
    public function activateTtariffActivity(Collection $tariffActivitys): array
    {
        $data = [];
        if ($this->isOldOrder()) {
            foreach ($tariffActivitys as $tariffActivity) {
                $tariffActivity->start_at = $this->order->captured_at;
                // активируем тариф
                if ($tariffActivity->order_id == $this->order->id && $tariffActivity->end_at >= $this->order->captured_at) {
                    $startAt = new Carbon($tariffActivity->start_at);
                    $tariffActivity->end_at = $startAt->addMonth();
                }
                $tariffActivity->status = self::ACTVE;

                $data[] = $tariffActivity->save();
            }
        }

        return $data;
    }

    /**
     * Деактивация подписки (после покупки нового  тарифа)
     * @param  Order  $order
     * @return void
     */
    public function deactivateSubscription(): void
    {
        if ($this->isOldOrder()) {
            TariffActivity::where('user_id', $this->order->user_id)
                ->where('order_id', '!=', $this->order->id)->update(['status' => TariffActivity::INACTVE]);
        }
    }

    /**
     * Проверка что обрабатывается заказ старой механики
     */
    private function isOldOrder()
    {
        if($this->order->tariff_ids === 'null'){
            return false;
        }
        return true;
    }
}
