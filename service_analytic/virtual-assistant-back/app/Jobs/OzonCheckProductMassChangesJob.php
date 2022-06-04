<?php

namespace App\Jobs;

use App\Models\ProductFeatureErrorHistory;
use App\Repositories\OzonProductRepository;
use App\Services\V2\OzonApi;
use App\Services\V2\ProductServiceUpdater;
use \App\Services\Ozon\OzonApiStatusService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OzonCheckProductMassChangesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $productIds,
        private array $userAccount,
        private array $user
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productOzonUpdateStatuses = [];

        $productRepository = new OzonProductRepository();
        $products = $productRepository->getProductCollectionByIds($this->productIds);

        $ozonApiClient = new OzonApi($this->userAccount['platform_client_id'], $this->userAccount['platform_api_key']);
        $ozonApiStatusService = \App::make(OzonApiStatusService::class);

        foreach ($products as $product) {
            $productInfoResponse = $ozonApiClient->repeat('getProductInfo', $product->sku_fbo);
            $productStatus = $ozonApiStatusService->checkProductUploadStatus(
                $productInfoResponse
            );

            if ($productStatus === "error" && $productStatus === "failed_validation") {
                $productOzonUpdateStatuses[$product->id] = 'error';
                $message = "Cannot update product in ozon {$product->id}, status - {$productStatus}";

                report($message);
                ExceptionHandlerHelper::logFail($message);

                continue;
            }

            if ($productStatus === 'moderating') {
                $productOzonUpdateStatuses[$product->id] = 'moderating';

                continue;
            }

            // @TODO need refactor
            //когда товар на модерации данные берутся из истории
            try {
                $serviceUpdater = new ProductServiceUpdater($product->id);
                $serviceUpdater->updateStatus($productStatus);
                $serviceUpdater->updateFeatures();

                // @TODO Refactor this code to service
                if ($product->changeHistory->count()) {
                    $lastHistory = $product->changeHistory->last();
                    $changesFeatures = $lastHistory->changedFeatures;
                    foreach ($changesFeatures as $changedFeature) {
                        if ($feature = $product->featuresValues->where('feature_id',
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

                if ($productStatus === 'processed') {
                    $productOzonUpdateStatuses[$product->id] = 'success';
                }

            } catch (\Exception $e) {
                report($e);
                ExceptionHandlerHelper::logFail($e);

            }
        }

        $countStatus = array_count_values($productOzonUpdateStatuses);

        if (isset($countStatus['moderating']) && $countStatus['moderating'] > 0) {
            $repeatIds = array_keys($productOzonUpdateStatuses, 'moderating');
            self::dispatch($repeatIds, $this->userAccount, $this->user)->delay(now()->addMinute());
        }

        if (isset($countStatus['success']) && $countStatus['success'] > 0) {
            UsersNotification::dispatch(
                'marketplace.account_product_update_success',
                [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                [
                    'count' => array_count_values($productOzonUpdateStatuses)['success'], 'total' => $products->count(),
                    'marketplace' => 'Ozon'
                ]
            );
        }
    }
}
