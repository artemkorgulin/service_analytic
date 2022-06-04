<?php

namespace App\Console;

use App\Console\Commands\LoadCampaignProductsFromOzon;
use App\Console\Commands\Ozon\DownloadCampaignReports;
use App\Console\Commands\Ozon\LoadCampaigns;
use App\Console\Commands\Ozon\RequestCampaignReports;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use RuntimeException;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RequestCampaignReports::class,
        DownloadCampaignReports::class,
        LoadCampaigns::class,
        LoadCampaignProductsFromOzon::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $logPath = sprintf('%s/logs/console/%s/', storage_path(), date('Y-m-d'));

        if (!is_dir($logPath)) {
            if (!mkdir($logPath, 0777, true) && !is_dir($logPath)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $logPath));
            }
        }

        /*
        $schedule->command('ozon:load-campaigns')
            ->dailyAt('01:00')
            ->description('Load Ozon campaigns')
            ->appendOutputTo($logPath . 'load_ozon_campaigns.log');

        $schedule->command('ozon:load-campaign-products')
            ->dailyAt('03:00')
            ->description('load Ozon campaign products')
            ->appendOutputTo($logPath . 'load_ozon_campaigns_products.log');

        $schedule->command('ozon:generate-campaign-reports ')
            ->dailyAt('04:00')
            ->description('generate Ozon campaigns reports')
            ->appendOutputTo($logPath . 'generate_ozon_ campaigns_reports.log');
        */


        /*
        $schedule->command('demo:cron')
                 ->everyMinute()
                ->description('demo:cron')
                ->appendOutputTo($logPath.'demo_cron.log');

        $schedule->command('load_from_ozon:campaigns_reports_response')
                 ->hourlyAt(30 + $syncDelay)->between('3:00', '23:59')
                 ->description('load-campaigns-reports')
                 ->appendOutputTo($logPath.'load_campaigns_reports_response.log');

        $schedule->command('load-from-va:popularities')
                 ->everyTwoHours()->between('3:00', '23:59')
                 ->description('load-popularities-from-va')
                 ->appendOutputTo($logPath.'load_popularities.log');

        $schedule->command('apply:strategies')
                 ->hourlyAt(0 + $syncDelay)->between('5:00', '23:00')
                 ->description('apply-strategies')
                 ->appendOutputTo($logPath.'apply_strategies.log');

        $schedule->command('autoselect:delete_old_results')
                 ->dailyAt('1:30')
                 ->description('delete-old-autoselect-results')
                 ->appendOutputTo($logPath.'delete_old_autoselect_results.log');*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
