<?php

namespace App\Console\Commands;

use App\Services\SearchQueryLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class LoadSearchQueriesCommand
 * Позволяет загрузить результаты обработки поисковых запросов из файла
 * @package App\Console\Commands
 */
class LoadSearchQueriesCommand extends Command
{
    /**
     * Префикс названия файла от парсинга
     */
    const FILE_NAME = 'seller_ozon_ru_results';
    /**
     * Расширение файла от парсинга
     */
    const FILE_EXT = 'csv';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:search_queries {--date=today} {--no-headers} {--no-totals}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить поисковые запросы из парсинга';

    /**
     * Execute the console command.
     *
     * @param SearchQueryLoader $loader
     */
    public function handle(SearchQueryLoader $loader)
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $filePath = config('sources.search_queries_input_path') .
            static::FILE_NAME . '_' . $date->format('Y.m.d') . '.' . static::FILE_EXT;

        echo $filePath . "\r\n";

        $processTitles = !$this->option('no-headers');
        $calcTotals = !$this->option('no-totals');

        $count = $loader->load($filePath, $processTitles, $calcTotals);
        $duration = $loader->getDuration();

        $errors = $loader->getErrors();
        if ($errors->isNotEmpty()) {
            echo __('import.has_errors', ['count' => $errors->count()]) . ":\r\n";
            foreach ($errors->all() as $message) {
                echo $message . "\r\n";
            }
        }

        if ($count) {
            echo __('import.file_has_been_processed', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'count' => $count,
                'hours' => $duration->h,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]);
        } else {
            echo __('import.file_processed_with_errors', [
                'datetime' => Carbon::now()->toDateTimeString(),
            ]);
        }
    }
}
