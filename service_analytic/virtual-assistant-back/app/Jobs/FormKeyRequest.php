<?php

namespace App\Jobs;

use App\Classes\WbKeyRequestHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FormKeyRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $productId;

    /**
     * Create a new job instance.
     *
     * @param  int  $productId
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $wbKeyRequestHandler->handle($this->productId);
    }
}
