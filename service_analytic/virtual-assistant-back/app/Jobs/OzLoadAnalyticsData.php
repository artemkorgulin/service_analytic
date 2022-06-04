<?php

namespace App\Jobs;

use App\Models\OzProduct;
use App\Services\InnerService;
use App\Services\Ozon\OzonAnalyticsDataService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OzLoadAnalyticsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var OzProduct */
    private OzProduct $product;

    /**
     * Create a new job instance.
     *
     * @param OzProduct $product
     */
    public function __construct(OzProduct $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $dateFrom = Carbon::now()->subMonth()->format('Y-m-d');
        $dateTo = Carbon::now()->format('Y-m-d');
        $userAccount = (new InnerService)->getAccount($this->product->account_id);
        if (!empty($userAccount)) {
            (new OzonAnalyticsDataService($userAccount))->updateMetrics($this->product, $dateFrom, $dateTo);
        }
    }
}
