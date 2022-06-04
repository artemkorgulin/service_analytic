<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка базы данных от всех кампаний';

    /**
     * Execute the console command
     */
    public function handle()
    {
        if (!App::environment('local')) {
            $this->error('Команда может быть запущена только на локальном компьютере и дев сервере');

            return Command::FAILURE;
        }

        $this->info("Запущена очистка базы");

        Schema::disableForeignKeyConstraints();
        DB::table('strategy_shows_keyword_statistics')->truncate();
        DB::table('strategy_histories')->truncate();
        DB::table('strategy_cpo_statistics')->truncate();
        DB::table('strategy_cpo_keyword_statistics')->truncate();
        DB::table('strategies_shows')->truncate();
        DB::table('strategies_cpo')->truncate();
        DB::table('strategies')->truncate();
        DB::table('groups')->truncate();
        DB::table('cron_uuid_report')->truncate();
        DB::table('campaign_stop_words')->truncate();
        DB::table('campaign_statistics')->truncate();
        DB::table('campaign_keyword_statistics')->truncate();
        DB::table('campaign_keywords')->truncate();
        DB::table('campaign_product_statistic')->truncate();
        DB::table('campaign_products')->truncate();
        DB::table('campaigns')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->info("Очистка базы завершена");

        return Command::SUCCESS;
    }
}
