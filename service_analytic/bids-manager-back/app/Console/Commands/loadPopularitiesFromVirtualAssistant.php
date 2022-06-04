<?php

namespace App\Console\Commands;

use App\Services\Loaders\VirtualAssistantPopularitiesLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

class loadPopularitiesFromVirtualAssistant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load-from-va:popularities {--campaign-ids=*} {--date-from=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка популярности из Виртуального помощника';

    /**
     * Execute the console command.
     *
     * @param VirtualAssistantPopularitiesLoader $loader
     */
    public function handle(VirtualAssistantPopularitiesLoader $loader)
    {
        $dateFrom = $this->option('date-from');
        $campaignIds = $this->option('campaign-ids');

        $loader->load(['campaignIds' => $campaignIds, 'dateFrom' => $dateFrom]);
        $duration = $loader->getDuration();

        if ($loader->getErrors()->isNotEmpty()) {
            echo __('loader.has_errors', ['count' => $loader->getErrors()->count()]) . "\r\n";
        }

        echo __('loader.finish_loading', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'hours' => $duration->h,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]) . "\r\n";
    }
}
