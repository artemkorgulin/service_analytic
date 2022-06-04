<?php

namespace App\Console\Commands\Ozon;

use App\Services\Loaders\OzonCampaignsLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LoadCampaigns extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка кампаний из Озона';


    /**
     * Execute the console command
     *
     * @param  OzonCampaignsLoader  $loader
     */
    public function handle(OzonCampaignsLoader $loader)
    {
        $loader->load();
        $duration = $loader->getDuration();

        if ($loader->getErrors()->isNotEmpty()) {
            $this->error(__('loader.has_errors', ['count' => $loader->getErrors()->count()]));
        }

        $this->info(__('loader.finish_loading', [
            'datetime' => Carbon::now()->toDateTimeString(),
            'hours'    => $duration->h,
            'min'      => $duration->i,
            'sec'      => $duration->s,
        ]));
    }
}
