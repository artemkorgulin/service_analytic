<?php

namespace App\Console\Commands;

use App\Jobs\DashboardUpdateJob;
use App\Models\OzProduct;
use App\Services\InnerService;
use App\Services\UserService;
use App\Services\V2\ProductServiceUpdater;
use Exception;
use Illuminate\Console\Command;

/**
 * Class V2LoadProducts
 * Позволяет выгрузить из озона информацию о всех товарах из БД
 * @package App\Console\Commands
 */
class V2LoadProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет информацию о товарах';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Ozon product update start');
        $products = OzProduct::all();
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();
        foreach ($products as $iter => $product) {
            if (!isset($product->account_id)) {
                continue;
            }
            try {
                (new ProductServiceUpdater($product->id, $product->account_id))->updateInfo();
            } catch (Exception $exception) {
                if ($exception->getCode() !== 404) {
                    report($exception);
                }
                $this->error('Fail updating product id=' . $product->id . ' #' . $exception->getCode() . ': ' . $exception->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info('Ozon product update completed');

        DashboardUpdateJob::dispatch(1)->delay(now()->addMinutes(3));
    }
}
