<?php

namespace App\Console;

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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $logPath = storage_path().'/logs/console/'.date('Y-m-d').'/';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
        }

        $schedule->command('history:migrate')->dailyAt('06:00')
            ->appendOutputTo($logPath.'history_migrate.log');
        $schedule->command('history:top36')->dailyAt('06:00')
            ->appendOutputTo($logPath.'history_top36.log');
        $schedule->command('oz_history:migrate')->dailyAt('05:00')
            ->appendOutputTo($logPath.'history_migrate.log');
        $schedule->command('oz_history:top36')->dailyAt('05:00')
            ->appendOutputTo($logPath.'history_top36.log');

        $schedule->command('warmup-cache:categories')->dailyAt('03:00')
            ->appendOutputTo($logPath.'warmup-cache_categories.log');
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
