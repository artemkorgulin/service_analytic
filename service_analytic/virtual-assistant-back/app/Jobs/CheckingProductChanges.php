<?php

namespace App\Jobs;

use App\Models\OzProduct;
use App\Models\ProductFeatureErrorHistory;
use App\Services\Ozon\OzonParsingService;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceUpdater;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckingProductChanges
 * Проверка статуса товара
 * @package App\Jobs
 */
class CheckingProductChanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var OzProduct */
    private $product;

    private bool $repeat;

    private array $user;

    /**
     * Create a new job instance.
     *
     * @param OzProduct $id
     */
    public function __construct(OzProduct $product, array $user, bool $repeat = false)
    {
        $this->product = $product;
        $this->repeat = $repeat;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return boolean
     */
    public function handle()
    {
        // TODO изменить получение пользователя с Web-app
        $account = $this->product->account();

        if (!$account || !isset($account['platform_client_id']) || !isset($account['platform_api_key'])) {
            return false;
        }

        $ozonApiClient = new OzonApi($account['platform_client_id'], $account['platform_api_key']);
        $productInfoResponse = $ozonApiClient->repeat('getProductInfo', $this->product->sku_fbo);

        if (!$productInfoResponse || $productInfoResponse['statusCode'] !== 200) {
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
            Log::info($productInfo['status']);
            $productStatus = $serviceUpdater->arrayToStatus($productInfo['status']);
            $serviceUpdater->updateStatus($productStatus);

            if (!$this->repeat) {
                if (!in_array($productStatus, ['error', 'failed_validation'])) {
                    UsersNotification::dispatch(
                        'card_product.marketplace_product_start_upload_success',
                        [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                        ['marketplace' => 'Ozon']
                    );
                } else {
                    UsersNotification::dispatch(
                        'card_product.marketplace_product_start_upload_fail',
                        [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                        ['marketplace' => 'Ozon']
                    );
                }
            }

            if ($productStatus === 'moderating') {
                self::dispatch($this->product, $this->user, true)->delay(now()->addMinute());
            }
            (new OzonParsingService)->createCategoryForProduct($this->product);
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        return true;
    }
}
