<?php

namespace App\Jobs;

use App\Models\OzProduct;
use App\Services\V2\OptimisationHistoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOzOptimisation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @param OzProduct $product
     */
    public function __construct(private OzProduct $product)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        (new OptimisationHistoryService)->updateOptimisationToOzon($this->product);
    }
}
