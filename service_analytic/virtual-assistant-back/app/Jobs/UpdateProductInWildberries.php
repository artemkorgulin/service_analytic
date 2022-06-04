<?php

namespace App\Jobs;

use App\Models\WbProduct;
use App\Services\UserService;
use App\Services\Wildberries\Client;
use App\Services\Wildberries\WildberriesStocksService;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateProductInWildberries implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    private $user;

    public int $tries = 4;

    private $notMassUpdate;

    const MIN_DISCOUNT_VALUE = 3;
    const MAX_DISCOUNT_VALUE = 90;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WbProduct $product, $user = null, $notMassUpdate = true)
    {
        $this->product = $product;
        $this->user = $user;
        $this->notMassUpdate = $notMassUpdate;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        return [60, 5 * 60, 15 * 60, 30 * 60];
    }

    /**
     * Send notification to user if job failed
     *
     * @return void
     */
    public function failed()
    {
        if ($this->notMassUpdate) {
            UsersNotification::dispatch(
                'card_product.marketplace_product_start_upload_fail',
                [['id' => $this->user['id'], 'lang' => 'ru', 'email' => $this->user['email']]],
                ['marketplace' => 'Wildberries']
            );
        } else {
            $this->product->update(['is_block' => 0]);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->product->imt_id && $this->product->account_id && $this->product->data) {

            $account = UserService::loadAccount($this->product->account_id);

            if (!isset($account['platform_api_key']) && !isset($account['platform_client_id'])) {
                Log::error("Account ID={$this->product->account_id} is not found in WebAppBack (WAB)");
                return;
            }

            $wbClient = new Client($account['platform_client_id'],
                $account['platform_api_key']);
            $data = $this->product->data;

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            if (is_object($data)) {
                $data = json_decode(json_encode($data), true);
            }

            $response = $wbClient->cardUpdate($data, $this->user);

            if (isset($response->id)) {
                $status = WbProduct::STATUS_SUCCESS;
            } else {
                $status = WbProduct::STATUS_ERROR;
            }
            $this->product->update(['status_id' => $status]);

            // Now make correction for Wildberries prices

            $nomenlatures = $this->product->nomenclatures()->select([
                'wb_nomenclatures.nm_id AS nmId',
                'wb_nomenclatures.price AS price'
            ])
                ->get()
                ->toArray();

            $newNomenlatures = [];
            foreach ($nomenlatures as $item) {
                $item['price'] = (int) $item['price'] > 0 ? (int) $item['price'] + 1 : (int) $item['price'];
                $newNomenlatures[] = $item;
            }

            $wbClient->setPrices($newNomenlatures);
            sleep(1);
            $wbClient->setPrices($nomenlatures);


            // Now make correction for Wildberries discounts
            $nomenlatures = $this->product->nomenclatures()->select([
                'wb_nomenclatures.nm_id AS nm',
                'wb_nomenclatures.discount AS discount'
            ])->where('discount', '>', self::MIN_DISCOUNT_VALUE)
                ->where('discount', '<', self::MAX_DISCOUNT_VALUE)
                ->limit(1000)
                ->get()
                ->toArray();

            $zeroDiscounts = [];
            if (!empty($nomenlatures)) {
                foreach ($nomenlatures as $key => $val) {
                    $zeroDiscounts[$key]['nm'] = $val['nm'];
                    $zeroDiscounts[$key]['discount'] = (int) $val['discount'] > 0 ? (int) $val['discount'] - 1 : (int) $val['discount'];
                }
                $wbClient->updateDiscounts($zeroDiscounts, true);
            }
            sleep(1);
            if (!empty($nomenlatures)) {
                foreach ($nomenlatures as $key => $val) {
                    $nomenlatures[$key]['discount'] = (int) floor($val['discount']);
                }
                $wbClient->updateDiscounts($nomenlatures, true);
            }

            $nomenlatures = $this->product->nomenclatures()->select([
                'wb_nomenclatures.nm_id AS nm',
                'wb_nomenclatures.discount AS discount'
            ])->where('discount', '<=', self::MIN_DISCOUNT_VALUE)
                ->where('discount', '>=', self::MAX_DISCOUNT_VALUE)
                ->pluck('nm')
                ->toArray();
            if (!empty($nomenlatures)) {
                $wbClient->revokeDiscounts($nomenlatures);
            }

            $WbProductStockService = new WildberriesStocksService();
            $WbProductStockService->updateProductStocks($this->product);

        }
        if (!$this->notMassUpdate) {
            $this->product->update(['is_block' => 0]);
        }
    }
}
