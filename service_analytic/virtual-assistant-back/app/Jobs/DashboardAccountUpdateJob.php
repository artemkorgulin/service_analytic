<?php

namespace App\Jobs;

use App\Services\ProductCommon\CommonProductDashboardService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DashboardAccountUpdateJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private CommonProductDashboardService $dashboardService;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public int $uniqueFor = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected int $userId,
        protected int $accountId,
        protected int $marketplacePlatformId
    ) {
        $this->dashboardService = new CommonProductDashboardService(
            $userId,
            $accountId,
            $marketplacePlatformId
        );

        $this->queue = 'default_long';
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->userId . '_' . $this->accountId . '_' . $this->marketplacePlatformId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            foreach (array_keys(config('model.dashboard.type')) as $dashboardType) {
                $this->dashboardService->updateOrCreateDashboard($dashboardType);
            }
        } catch (\Exception $e) {
            report($e);
            ExceptionHandlerHelper::logFail($e);
        }
    }
}
