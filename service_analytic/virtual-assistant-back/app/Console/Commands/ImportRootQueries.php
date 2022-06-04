<?php

namespace App\Console\Commands;

use App\Services\RootQueryLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class ImportRootQueries
 * Позволяет загрузить корневые запросы из файла
 * @package App\Console\Commands
 */
class ImportRootQueries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:root-queries {path} {--no-headers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка корневых запросов';

    /**
     * Execute the console command.
     *
     * @param RootQueryLoader $loader
     */
    public function handle(RootQueryLoader $loader)
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
