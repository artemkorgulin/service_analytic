<?php

namespace App\Jobs;

use App\Models\WbDirectoryItem;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrCreateWbDirectoryExtItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data = [];

    private $feature;

    private $extDirectory;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $feature)
    {
        $this->data = $data;
        $this->feature = $feature;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->feature->items()->createMany(
                $this->data
            );
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

    }
}
