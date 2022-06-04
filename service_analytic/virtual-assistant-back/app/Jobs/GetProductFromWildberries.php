<?php

namespace App\Jobs;

use App\Classes\Helper;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Services\UserService;
use App\Services\Wildberries\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetProductFromWildberries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WbProduct $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void | bool
     */
    public function handle()
    {
        if ($this->product->imt_id && $this->product->account_id) {
            $account = UserService::loadAccount($this->product->account_id);
            if ($account && isset($account['platform_client_id']) && isset($account['platform_api_key'])) {
                $wbClient = new Client($account['platform_client_id'],
                    $account['platform_api_key']);
                $response = $wbClient->getCardByImtId($this->product->imt_id);
                $card = $response['result']['card'] ?? null;
                if (!$card) {
                    return false;
                }
                // Step-1 refresh nomenclatures
                $nomenclatureNmIds = Helper::wbCardGetNmIds($card);
                Log::channel('queues')->info(print_r($nomenclatureNmIds, true));
                $nomenclatureIds = WbNomenclature::where('account_id', $account['id'])
                    ->whereIn('nm_id', $nomenclatureNmIds)
                    ->pluck('id')->toArray();
                $this->product->nomenclatures()->sync($nomenclatureIds);
                // Step-2 update product
                $this->product->update(['account_id' => $account['id'],
                    'card_id' => $card['id'],
                    'imt_id' => $card['imtId'],
                    'card_user_id' => $card['userId'],
                    'supplier_id' => $card['supplierId'],
                    'imt_supplier_id' => $card['imtSupplierId'],
                    'title' => Helper::wbCardGetTitle($card),
                    'object' => $card['object'],
                    'parent' => $card['parent'],
                    'country_production' => $card['countryProduction'],
                    'supplier_vendor_code' => $card['supplierVendorCode'],
                    'data' => json_encode($card)]);
                $this->product->save();
            }
        }
    }
}
