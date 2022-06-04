<?php

namespace App\Console\Commands;

use App\Services\Loaders\OzonCampaignProductsLoader;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LoadCampaignProductsFromOzon extends Command
{
    use AcceptsCommaSeparatedArrayOptions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load-campaign-products {--campaign-ids= : Id кампаний, по которым нужно выполнить загрузку товаров через запятую.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка товаров рекламных кампаний из Озона';

    /**
     * Execute the console command
     *
     * @param OzonCampaignProductsLoader $loader
     */
    public function handle(OzonCampaignProductsLoader $loader)
    {
        $campaignIds = $this->getArrayOption('campaign-ids');
        $loader->load(['campaignIds' => $campaignIds]);
        $duration = $loader->getDuration();

        if( $loader->getErrors()->isNotEmpty() ) {
            echo __('loader.has_errors', ['count' => $loader->getErrors()->count()])."\r\n";
        }

        echo __('loader.finish_loading', [
            'datetime' => Carbon::now()->toDateTimeString(),
            'hours' => $duration->h,
            'min' => $duration->i,
            'sec' => $duration->s,
        ])."\r\n";
    }
}
