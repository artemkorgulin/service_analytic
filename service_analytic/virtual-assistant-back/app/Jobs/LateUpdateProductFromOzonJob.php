<?php

namespace App\Jobs;

use App\Models\OzProduct;
use App\Models\ProductFeatureErrorHistory;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceUpdater;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LateUpdateProductFromOzonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  OzProduct  $product
     * @param  bool  $repeat
     * @return void
     */
    public function __construct(private OzProduct $product, private bool $repeat = false)
    {
        //
    }


    /**
     * @return bool
     */
    public function handle()
    {
        // TODO изменить получение пользователя с Web-app
        $account = $this->product->account();
        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $productInfoResponse = $ozonApiClient->repeat('getProductInfo', $this->product->sku_fbo);

        if ($productInfoResponse['statusCode'] !== 200) {
            return false;
        }

        // TODO изменить получение пользователя с Web-app
        $account = $this->product->account();
        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $productInfoResponse = $ozonApiClient->repeat('getProductInfo', $this->product->sku_fbo);

        if ($productInfoResponse['statusCode'] !== 200) {
            return false;
        }

        //когда товар на модерации данные берутся из истории
        try {
            $serviceUpdater = new ProductServiceUpdater($this->product->id);
            $serviceUpdater->updateFeatures();
            if ($this->product->changeHistory->count()) {
                $lastHistory = $this->product->changeHistory->last();
                $changesFeatures = $lastHistory->changedFeatures;
                foreach ($changesFeatures as $changedFeature) {
                    if ($feature = $this->product->featuresValues->where('feature_id',
                        $changedFeature->feature_id)->first()) {
                        if ($feature->value === $changedFeature->value) {
                            continue;
                        }
                    }

                    $productFeatureErrorHistory = new ProductFeatureErrorHistory();
                    $productFeatureErrorHistory->feature_id = $changedFeature->feature_id;
                    $productFeatureErrorHistory->history_id = $changedFeature->history_id;
                    $productFeatureErrorHistory->save();
                }
            }

            $productInfo = $productInfoResponse['data']['result'];
            $productStatus = $serviceUpdater->arrayToStatus($productInfo['status']);
            $serviceUpdater->updateStatus($productStatus);

            if ($productStatus === 'moderating') {
                self::dispatch($this->product, true)->delay(now()->addMinute());
            }
        } catch (\Exception $e) {
            report($e);
            ExceptionHandlerHelper::logFail($e);

        }

        return true;
    }
}
