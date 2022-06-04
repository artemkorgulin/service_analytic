<?php

namespace App\Jobs;

use App\Models\OzProduct;
use App\Models\OzTemporaryProduct;
use App\Services\Ozon\OzonParsingService;
use App\Services\V2\FtpService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CreateOzProductFromTemporary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public OzTemporaryProduct $temporaryProduct;
    private OzProduct $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OzTemporaryProduct $temporaryProduct, OzProduct $product)
    {
        $this->temporaryProduct = $temporaryProduct;
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OzonParsingService $ozonParsingService)
    {
        try {
            (new FtpService())->sendSkuRequestFile();
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        // Добавляем данные в таблицу семантического ядра
        if ($this->product->id) {
            $ozonParsingService->createCategoryForProduct($this->product);
            OzLoadAnalyticsData::dispatch($this->product);
            Artisan::call('ozon:semantic', ['id' => $this->product->id]);
        }

    }
}
