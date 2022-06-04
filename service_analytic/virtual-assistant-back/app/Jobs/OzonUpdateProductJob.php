<?php

namespace App\Jobs;

use App\Services\V2\ProductServiceUpdater;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OzonUpdateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $updateProductArray;
    private $productService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($updateProductArray)
    {
        $this->productService = new ProductServiceUpdater($updateProductArray['id']);
        $this->updateProductArray = $updateProductArray;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->productService->updateFromArray($this->updateProductArray);
        } catch (\Exception $e) {
            report($e);
            ExceptionHandlerHelper::logFail($e);
        }
    }
}
