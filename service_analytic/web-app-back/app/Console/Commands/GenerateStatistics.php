<?php

namespace App\Console\Commands;

use App\Services\UserStatisticsService;
use Illuminate\Console\Command;

class GenerateStatistics extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:generate 
                            {--P|with-previous : Also generate statistics for all dates before the given date} 
                            {date=yesterday : Date string acceptable for the DateTime constructor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gathers user and payment statistics and saves it to the database.';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $withPrevious = $this->option('with-previous');
        $date = strtotime($this->argument('date'));

        $message = 'Generating statistics for the date '.date('d.m.Y', $date);
        if ($withPrevious) {
            $message .= ' and all previous dates';
        }

        $this->info($message);
        $statisticsService = new UserStatisticsService(date(UserStatisticsService::DATE_FORMAT, $date), $withPrevious ? '<=' : '=');

        $this->info(
            sprintf(
                'Save completed. Rows saved: %d. (Updated rows are counted twice).',
                $statisticsService->saveStatistics()
            )
        );
    }
}
