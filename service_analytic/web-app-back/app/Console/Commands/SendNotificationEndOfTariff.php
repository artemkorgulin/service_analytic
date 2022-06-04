<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\TariffActivity;
use App\Models\User;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Console\Command;

class SendNotificationEndOfTariff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:endTariff {countDay=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить уведомление об окончании тарифа';
    private mixed $testUser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->testUser = env('TEST_USER', 15);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $countDay = $this->argument('countDay');
        $from = Carbon::now()->addDays($countDay - 1)->toDateTimeString();
        $to = Carbon::now()->addDays($countDay)->toDateTimeString();

        $tariffActivitys = TariffActivity::query()
            ->where('status', '=', TariffActivity::ACTVE)
            ->whereBetween('end_at', [$from, $to])
            ->groupBy('order_id')
            ->get();

        foreach ($tariffActivitys as $tariffActivity) {
            $oderId = $tariffActivity->order_id;

            if (!self::isTariffWattInOrder($oderId)) {
                $this->info('Отправляем сообщение обо кончание тарифа. orderId: '.$oderId);

                self::sendNotification($oderId, $countDay);
            } else {

                $this->info('Тарифы ожидают активации orderId'.$oderId);
            }
        }

        return Command::SUCCESS;
    }


    /**
     * @param $oderId
     * @return bool
     */
    public function isTariffWattInOrder($oderId): bool
    {
        return (bool) TariffActivity::query()
            ->where('status', '=', TariffActivity::WAIT)
            ->where('order_id', $oderId)
            ->get()
            ->count();
    }

    /**
     * @param $orderId
     * @param $countDay
     * @return void
     */
    public function sendNotification($orderId, $countDay): void
    {
        $order = Order::query()->whereId($orderId)->first();
        $user = User::query()->whereId($order->user_id)->first();

        UsersNotification::dispatch(
            'technical.subscription_end_days',
            [
                ['id' => $order->user_id, 'lang' => 'ru', 'email' => $user->email],
                ['id' => $this->testUser, 'lang' => 'ru', 'email' => 's.garbusha@yandex.ru'],
            ],
            ['days' => $countDay]
        );
    }
}
