<?php

namespace App\Jobs\Wildberries;

use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WildberriesClearPriceJsonFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return 'WildberriesClearPriceJsonFilesJob';
    }

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        $this->disk = 'json';
    }

    /**
     * Execute the job.
     *
     * @return boolean
     */
    public function handle()
    {
        try {
            $allFiles = Storage::disk($this->disk)->allFiles();
            if ($allFiles) {
                Storage::disk($this->disk)->delete($allFiles);
            }
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
    }
}
