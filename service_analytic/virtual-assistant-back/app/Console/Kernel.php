<?php

namespace App\Console;

use App\Jobs\DashboardUpdateJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('console.disable_console_scheduler')) {
            return;
        }

        $logPath = storage_path() . '/logs/console/' . date('Y-m-d') . '/';
        $baseLogFile = $logPath . 'commands' . date('Y-m-d') . '.log';

        if (!is_dir($logPath) && !mkdir($logPath, 0777, true) && !is_dir($logPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $logPath));
        }

        // Команда для проверки создания нового аккаунта
        $schedule->command('account:create')->everyFiveMinutes()->appendOutputTo($baseLogFile);

        $schedule->command('load:search_queries')->dailyAt('12:00')
            ->appendOutputTo($logPath . 'load_search_queries-load-root.log');
        $schedule->command('export:search_queries_weekly')->wednesdays()->at('00:10')
            ->appendOutputTo($logPath . 'load_search_queries-export-search.log');
        $schedule->command('load:weekly_search_queries')->thursdays()->at('00:10')
            ->appendOutputTo($logPath.'load_search_queries-load-search.log');

        // загрузка категорий Ozon по воскресеньям с 3 до 4. Повторять каждые 5 минут, пока не будет успешно
        $schedule->command('ozon:load_categories')
            ->sundays()
            ->everyFiveMinutes()
            ->between('3:00', '4:00')
            ->skip(function () {
                if ($existRow = DB::table('successful_tasks')->where('name', 'load_categories')
                    ->whereRaw('Date(created_at) = CURDATE()')->first()) {
                    return true;
                }

                return false;
            })
            ->onSuccess(function () {
                DB::table('successful_tasks')->insert(
                    ['name' => 'load_categories', 'created_at' => new \DateTime()]
                );
            })
            ->appendOutputTo($logPath.'load_ozon-load-categories.log');

        // загрузка категорий Wildberries по воскресеньям в 3:50
        $schedule->command('wb:get-product-categories')
            ->sundays()
            ->at('3:50');

        // загрузка списка характеристик по воскресеньям начиная с 3:30
        $schedule->command('ozon:load_features')
            ->sundays()
            ->at('3:30')
            ->appendOutputTo($logPath.'load_ozon-load-features.log');

        // загрузка информации о товарах Ozon
        $schedule->command('ozon:load_products')
            ->dailyAt('3:15')
            ->appendOutputTo($logPath . 'load_ozon-load-products.log');
        // загрузка информации о товарах Wildberries
        $schedule->command('wb:load_products')
            ->dailyAt('4:15')
            ->appendOutputTo($logPath . 'load_wildberries-load-products.log');

        // загрузка информации о характеристиках товаров
        $schedule->command('ozon:load_products_features')
            ->dailyAt('3:30')
            ->appendOutputTo($logPath.'load_ozon-load-products-features.log');

        // загрузка аналитики озон товаров
        $schedule->command('ozon:analytics-data')
            ->dailyAt('3:45')
            ->appendOutputTo($logPath.'load_analytics_for_ozon_products.log');


        // загрузка информации о позициях товаров
        $schedule->command('ozon:load_products_stats')
            ->dailyAt('6:00')
            ->appendOutputTo($logPath.'load_ozon-load-products-stats.log');

        // Получение результата парсинга карточек товаров
        $schedule->command('ftp:parse_sku')
            ->hourly()
            ->appendOutputTo($logPath.'load_ozon-ftp-parse-sku.log');

        // Получение результата парсинга топа категорий
        $schedule->command('ftp:parse_top')
            ->hourly()
            ->appendOutputTo($logPath.'load_ozon-ftp-parse-top.log');

        // изменения в характеристиках
        $schedule->command('notify:new_features')
            ->tuesdays()
            ->at('8:00')
            ->appendOutputTo($logPath.'notify-new-features.log');

        // Запуск команды которая обновляет статистические данные по опциям товаров
        $schedule->command('ozon:get-option-stats')
            ->mondays()
            ->at('16:42')
            ->appendOutputTo($logPath.'ozon_get-option-stats.log');

        // Запускаем обновление категорий товаров Wildberries
        $schedule->command('wb:get-product-categories')->dailyAt('2:30')->appendOutputTo($logPath . 'wb_get-product-categories.log');
        // А теперь подтягиваем все характеристики (словари) Wilberries
        $schedule->command('wb:get-directories')->sundays()->at('4:30')->appendOutputTo($logPath . 'wb_get-directories.log');
        $schedule->command('wb:get-directories')->wednesdays()->at('4:30')->appendOutputTo($logPath . 'wb_get-directories.log');

        // Add get top-36 analytics data from Rufago and export it to oz_product_top36s table
        $schedule->command('ozon:get-top36-analytic')->dailyAt('00:05')->appendOutputTo($logPath . 'oz_get-top36.log');

        // changes in optimisation histories
        $schedule->command('optimisation:create-history')
            ->dailyAt('2:15')
            ->appendOutputTo($logPath . 'optimisation-histories.log');

        //отправляем ключевые слова в rabbitmq парсера.
        $schedule->command('wb_using_keywords_parser:send')->dailyAt('19:00')->appendOutputTo($logPath . 'wb-using-keywords-parser.log');
        $schedule->command('ozon:send-using-keywords-parser')->dailyAt('19:00')->appendOutputTo($logPath . 'ozon-send-using-keywords-parser.log');

        // Ozon and Wildberries temporary product update
        $schedule->command('wb:update-products-for-accounts')->dailyAt('05:05')->appendOutputTo($logPath . 'wildberries-update-products-for-accounts.log');
        $schedule->command('ozon:update-products-for-accounts')->dailyAt('05:15')->appendOutputTo($logPath . 'ozon-update-products-for-accounts.log');

        $schedule->command('account:purge')->dailyAt('23:10')
            ->appendOutputTo($logPath . 'account_purge.log');

        $schedule->command('oz_products_quantity:refresh')->dailyAt('03:30')->appendOutputTo($logPath . 'oz_products_quantity_refresh.log');

        $schedule->command('ozon:update-stocks')->dailyAt('03:30')->appendOutputTo($logPath . 'ozon_update_stocks.log');

        $schedule->command('wb:update-stocks')->dailyAt('03:50')->appendOutputTo($logPath . 'wb_update_stocks.log');

        $schedule->command('ozon:load_options --max_coroutines=20')->twiceMonthly(15, 25, '01:22')->appendOutputTo($logPath . 'ozon_load_options.log');

        // Формирование семантического ядра Rufago
        $schedule->command('analytic:semantic')->fridays()->at('06:00')->appendOutputTo($logPath . 'analytic_semantic.log');;
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
