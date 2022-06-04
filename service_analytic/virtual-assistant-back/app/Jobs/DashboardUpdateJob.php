<?php

namespace App\Jobs;

use App\Services\InnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\DashboardAccountUpdateJob;

class DashboardUpdateJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public int $uniqueFor = 120;

    /**
     * @var int
     */
    private int $marketplaceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->marketplaceId . '_key';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InnerService $innerService)
    {

        $getAllUsersAndAccounts = $innerService->getAllSellerAccounts($this->marketplaceId);

        if (isset($getAllUsersAndAccounts['error']) || $getAllUsersAndAccounts === null) {
            throw new \Exception(
                'Ошибка получения данных аккаунтов.' . json_encode($getAllUsersAndAccounts, JSON_UNESCAPED_UNICODE)
            );
        }

        foreach ($getAllUsersAndAccounts as $account) {

            $checkMarketplace = array_search(
                $account['platform_id'],
                array_column(config('model.dashboard.repositories'), 'marketplace_id')
            );

            if ($checkMarketplace === false) {
                continue;
            }

            if (!isset($account['user_id']) || !$account['user_id']) {
                continue;
            }

            DashboardAccountUpdateJob::dispatch(
                $account['user_id'],
                $account['id'],
                $this->marketplaceId
            );
        }
    }

    /**
     * @return int
     */
    public function getMarketplaceId(): int
    {
        return $this->marketplaceId;
    }
}
