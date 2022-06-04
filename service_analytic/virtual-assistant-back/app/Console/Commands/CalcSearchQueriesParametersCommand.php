<?php

namespace App\Console\Commands;

use App\Services\SearchQueryLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class CalcSearchQueriesParametersCommand
 * Позволяет расчитать показатели поисковых запросов отдельно
 * @package App\Console\Commands
 */
class CalcSearchQueriesParametersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:search_queries_parameters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Расчитать показатели поисковых запросов';

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
     * @param SearchQueryLoader $loader
     */
    public function handle(SearchQueryLoader $loader)
    {
        $loader->calcSearchQueriesParams();
        $duration = $loader->getDuration();

        echo __('import.params_has_been_calculated', [
            'datetime' => Carbon::now()->toDateTimeString(),
            'hours' => $duration->h,
            'min' => $duration->i,
            'sec' => $duration->s,
        ]);
    }
}
