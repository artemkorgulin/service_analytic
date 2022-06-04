<?php

namespace App\Events\Ozon;

use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignListDTO;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountCampaignsLoadingFinished
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CampaignListDTO $campaignList, int $accountId)
    {
        //
    }
}
