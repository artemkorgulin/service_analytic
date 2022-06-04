<?php

namespace App\Jobs\Ozon;

use App\Models\Campaign;
use App\Services\OzonPerformanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateCampaignKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Campaign
     */
    public Campaign $campaign;

    /**
     * @var array
     */
    public array $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign, array $account)
    {
        $this->campaign = $campaign->withoutRelations();
        $this->account = $account;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = OzonPerformanceService::connectRepeat($this->account);
        Log::info('Подключение к Озон выполнено. Кампания '.$this->campaign->name.' - '.$this->campaign->id.'. Токен '.print_r($result));

        /*if ($this->campaign->ozon_id) {
            $ozonResult = (new OzonHelper)->ozonUpdateWords($this->campaign);
            $error = OzonPerformanceService::getLastError();

            if ($error) {
                Log::error($error . __('front.ozon_error'));
            }

            Log::info($ozonResult);
        }*/
    }
}
