<?php

namespace App\Console\Commands;

use App\Services\NegativeKeywordLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class ImportNegativeKeywords
 * Импорт минус слов
 * @package App\Console\Commands
 */
class ImportNegativeKeywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:negative-keywords {path} {--no-headers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка минус-слов';

    /**
     * Execute the console command.
     *
     * @param NegativeKeywordLoader $loader
     */
    public function handle(NegativeKeywordLoader $loader)
    {
        $path = $this->argument('path');
        $this->info("Импортируем из {$path}");

        $processTitles = !$this->option('no-headers');

        $count = $loader->load($path, $processTitles);
        $duration = $loader->getDuration();

        if ($loader->getErrors()->isNotEmpty()) {
            echo __('import.has_errors', ['count' => $loader->getErrors()->count()]) . "\r\n";
        }

        echo __('import.file_has_been_processed', [
            'datetime' => Carbon::now()->toDateTimeString(),
            'count' => $count,
            'hours' => $duration->h,
            'min' => $duration->i,
            'sec' => $duration->s,
        ]);
    }
}
