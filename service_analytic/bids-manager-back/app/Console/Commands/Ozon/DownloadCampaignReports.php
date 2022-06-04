<?php

namespace App\Console\Commands\Ozon;

use App\Console\Commands\AcceptsCommaSeparatedArrayOptions;
use App\Services\Loaders\OzonCampaignsReportsResponseLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DownloadCampaignReports extends Command
{

    use AcceptsCommaSeparatedArrayOptions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:download-campaign-reports
                                {--A|--accounts= : Account ids, e.g. 1,2,3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивание файлов готовых отчётов';


    /**
     * Execute the console command.
     *
     * @param  OzonCampaignsReportsResponseLoader  $loader
     */
    public function handle(OzonCampaignsReportsResponseLoader $loader)
    {
        $accountIds = $this->getArrayOption('accounts');
        $loader->setAccountIds($accountIds);

        $loader->load();
        $duration = $loader->getDuration();

        if ($loader->getErrors()->isNotEmpty()) {
            $this->info(__('loader.has_errors', ['count' => $loader->getErrors()->count()]));
        }

        $this->info(__('loader.finish_loading', [
            'datetime' => Carbon::now()->toDateTimeString(),
            'hours'    => $duration->h,
            'min'      => $duration->i,
            'sec'      => $duration->s,
        ]));
    }
}
