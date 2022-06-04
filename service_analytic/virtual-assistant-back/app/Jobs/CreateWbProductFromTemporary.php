<?php

namespace App\Jobs;

use App\Classes\Helper;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWbProductFromTemporary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * For store temporary product
     * @var
     */
    public WbTemporaryProduct $temporaryProduct;

    /** @var int|mixed account for add new products (must include user_id) */
    public $account;
    private WbProduct $newWbProduct;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WbTemporaryProduct $temporaryProduct, WbProduct $newWbProduct)
    {
        $this->temporaryProduct = $temporaryProduct;
        $this->newWbProduct = $newWbProduct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Resync Wildberries product
        GetProductFromWildberries::dispatch($this->newWbProduct);

        // Get real renovated (reloaded) product from Wildberries
        $productRealNew = optional(WbProduct::find($this->newWbProduct->id));

        // WbSync nomenclatures
        $nomenclatureNmIds = Helper::wbCardGetNmIds($productRealNew->data);

        if (!is_null($productRealNew->nomenclatures()) && $productRealNew->nomenclatures()->exists()) {
            $nomenclatureIds = WbNomenclature::where('account_id', $this->temporaryProduct->account_id)
                ->whereIn('nm_id', $nomenclatureNmIds)
                ->pluck('id')->toArray();

            $productRealNew->nomenclatures()->sync($nomenclatureIds);
        }

        if (!$productRealNew->nomenclatures()) {
            return;
        }
    }
}
