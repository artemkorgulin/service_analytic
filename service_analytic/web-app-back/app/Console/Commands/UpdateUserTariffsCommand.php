<?php

namespace App\Console\Commands;

use App\Models\TariffActivity;
use App\Models\User;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class UpdateUserTariffsCommand
 * Актуализация подписок пользователей.
 * Если срок действия подписки закончился меняет ее на бесплатную
 * @package App\Console\Commands
 */
class UpdateUserTariffsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tariffs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Актуализация подписок пользователей';
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
        $users = User::query()->cursor();
        $now = Carbon::now();

        foreach ($users as $user) {
            $tariffActivities = $user->tariffActivities
                ->whereIn('status', [TariffActivity::ACTVE, TariffActivity::WAIT]);

            foreach ($tariffActivities as $tariffActivity) {
                $startAt = Carbon::parse($tariffActivity->start_at);
                $endAt = Carbon::parse($tariffActivity->end_at);

                // Активирован тариф
                if ($tariffActivity->status === TariffActivity::WAIT && ($now > $startAt)) {
                    $tariffActivity->status = TariffActivity::ACTVE;
                    $tariffActivity->save();
                    $this->info("Активирован тариф $tariffActivity->tariff_id  tariff activity id $tariffActivity->id у пользователя $tariffActivity->user_id ");
                }

                // Деактивирован тариф
                if ($tariffActivity->status === TariffActivity::ACTVE && ($now > $endAt)) {
                    $tariffActivity->status = TariffActivity::INACTVE;
                    $tariffActivity->save();

                    self::sendNotification($tariffActivity);

                    $this->info("Деактивирован тариф $tariffActivity->tariff_id  tariff activity id $tariffActivity->id у пользователя $tariffActivity->user_id ");
                }
            }
            Cache::forget('proxy_user_tariffs_'.$user->id);
        }

        $this->info('Подписки пользователей актуализированы');

        return Command::SUCCESS;
    }

    /**
     * @param  TariffActivity  $tariffActivity
     * @return void
     */
    public function sendNotification(TariffActivity $tariffActivity): void
    {
        $user = User::query()->whereId($tariffActivity->user_id)->first();

        UsersNotification::dispatch(
            'technical.tarif_ended',
            [
                ['id' => $tariffActivity->user_id, 'lang' => 'ru', 'email' => $user->email],
                ['id' => $this->testUser, 'lang' => 'ru', 'email' => 's.garbusha@yandex.ru'],
            ],
        );
    }
}
