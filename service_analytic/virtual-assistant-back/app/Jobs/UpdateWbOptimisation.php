<?php

namespace App\Jobs;

use App\Models\WbProduct;
use App\Services\V2\OptimisationHistoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateWbOptimisation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    /** @var WbProduct */
    private WbProduct $product;

    /**
     * Create a new job instance.
     *
     * @param WbProduct $product
     */
    public function __construct(WbProduct $product)
    {
        $this->product = WbProduct::find($product->id);
        $this->onQueue('default_long');
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (!$this->product) {
            return;
        }
        (new OptimisationHistoryService)->updateOptimisationToWildberries($this->product);
    }
}
