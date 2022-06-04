<?php

namespace App\Console;

use App\Console\Commands\CreatePermissionCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        CreatePermissionCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $logPath = storage_path().'/logs/console/'.date('Y-m-d').'/';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
        }

        $schedule->command('send:endTariff 1')->dailyAt('00:00')->appendOutputTo($logPath.'send-end-tariff_1.log');
        $schedule->command('send:endTariff 3')->dailyAt('00:00')->appendOutputTo($logPath.'send-end-tariff_3.log');
        $schedule->command('send:endTariff 7')->dailyAt('00:00')->appendOutputTo($logPath.'send-end-tariff_7.log');
        $schedule->command('activate:invoiceInBank')->everyTenMinutes()->appendOutputTo($logPath.'update-invoice-in-bank.log');
        $schedule->command('update:tariffs')->dailyAt('00:00')->appendOutputTo($logPath.'update-tariffs.log');
        $schedule->command('statistics:generate')->dailyAt('00:00')->appendOutputTo($logPath.'generate-statistics.log');
        $schedule->command('companies:handle-after-end-corporate-tariff')->dailyAt('00:30')->appendOutputTo($logPath.'handle-companies-after-end-corporate-tariff.log');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
