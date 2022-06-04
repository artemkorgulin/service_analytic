<?php

namespace App\Jobs;

use App\Models\ProductChangeHistory;
use App\Services\UserService;
use App\Services\V2\OzonApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOzonProductStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oz_product_change_history;

    protected $moderationStatus = [
        'processed' => 1,
        'moderating' => 2,
        'processing' => 2,
        'imported' => 2,
        'failed_moderation' => 3,
        'failed_validation' => 3,
        'failed' => 3,
    ];

    protected $ozon_product_detail_link = 'https://www.ozon.ru/context/detail/id/';

    /**
     * Create a new job instance.
     *
     * @param ProductChangeHistory $oz_product_change_history
     */
    public function __construct(ProductChangeHistory $oz_product_change_history)
    {
        $this->oz_product_change_history = $oz_product_change_history;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $account_id = $this->oz_product_change_history->product->account_id;
        $account = UserService::getAccount($account_id);
        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $result = $ozonApiClient->getProductImportInfo($this->oz_product_change_history->task_id);
        if (isset($result['result']['items']['0']['status'])) {
            $ozonStatus = $result['result']['items']['0']['status'];
        } else {
            $ozonStatus = '';
        }

        $statusId = null;
        if ($ozonStatus) {
            $statusId = $this->moderationStatus[$ozonStatus] ?? null;
        }

        $this->oz_product_change_history->statuses()->create([
            'response_data' => $result,
            'request_data' => ['task_id' => $this->oz_product_change_history->task_id],
            'text_status' => $ozonStatus,
            'status_id' => $statusId
        ]);

        if ($statusId) {
            $this->oz_product_change_history->status_id = $statusId;
            $this->oz_product_change_history->is_send = 0;
            $this->oz_product_change_history->product->status_id = $statusId;

            if (isset($result['result']['items']['0']['product_id']) && $result['result']['items']['0']['product_id']) {
                $this->oz_product_change_history->product->external_id = $result['result']['items']['0']['product_id'];

                $response = $ozonApiClient->getProductInfoById($result['result']['items']['0']['product_id']);
                $skuFbo = $response['data']['result']['fbo_sku'] ?? null;
                $skuFbs = $response['data']['result']['fbs_sku'] ?? null;
                if (!empty($skuFbo)) {
                    $this->oz_product_change_history->product->url = 'https://www.ozon.ru/context/detail/id/' . $skuFbo;
                    $this->oz_product_change_history->product->sku_fbo = $skuFbo;
                }
                if (!empty($skuFbs)) {
                    $this->oz_product_change_history->product->sku_fbs = $skuFbs;
                }

                $this->oz_product_change_history->product->save();
            }
        }

        $this->oz_product_change_history->save();
    }
}
