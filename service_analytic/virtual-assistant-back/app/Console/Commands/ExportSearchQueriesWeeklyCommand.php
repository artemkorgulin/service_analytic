<?php

namespace App\Console\Commands;

use App\Services\SearchQueryCalcService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class ExportSearchQueriesWeeklyCommand
 * Позволяет сформировать еженедельный файл для парсинга
 * с поисковыми запросами, требующими дополнительной обработки
 * @package App\Console\Commands
 */
class ExportSearchQueriesWeeklyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:search_queries_weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Составить недельный файл для парсинга на основе Коэффициента отсеивания поисковых запросов';

    /**
     * Execute the console command.
     *
     * @param SearchQueryCalcService $calcService
     */
    public function handle(SearchQueryCalcService $calcService)
    {
        $calcService->makeWeeklyParsingRequestFile();
        $duration = $calcService->getDuration();
        $errors = $calcService->getErrors();
        if ($errors->isNotEmpty()) {
            $this->error(__('parsing.has_errors', ['count' => $errors->count()]) . ':');
            foreach ($errors->all() as $message) {
                $this->error($message);
                Log::channel('import_request')->error($message);
            }
        } else {
            $this->info(__('parsing.sqfr_has_been_calculated', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'hours' => $duration->h,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]));
        }
    }
}
