<?php

namespace App\Console\Commands;

use App\Helpers\ProductHelper;
use App\Models\OzProduct;
use App\Services\V2\ProductsFeatureServiceUpdater;
use Exception;
use Illuminate\Console\Command;

/**
 * Class V2LoadProductsFeatures
 * Позволяет выгрузить из озона значения характеристик для всех товаров из БД
 * @package App\Console\Commands
 */
class V2LoadProductsFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_products_features';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет информацию о характеристиках товара';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Обновление характеристик товаров из Озона');

//        $products = ProductHelper::getProductListForLoading();
        $products = OzProduct::all();//TODO убрать
        $productsByUser = $products->groupBy('user_id');
        $bar = $this->output->createProgressBar(count($productsByUser));
        $bar->start();
        foreach ($productsByUser as $products) {
            try {
                $chunkedProducts = $products->chunk(100);
                foreach ($chunkedProducts as $productsChunk) {
                    $productsFeatureServiceUpdate = new ProductsFeatureServiceUpdater($productsChunk);
                    $productsFeatureServiceUpdate->updateFeatures();
                }
            } catch (Exception $e) {
                $this->info('Произошла ошибка при обновлении характеристик товаров: ' . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info('Обновление характеристик товаров из Озона завершено');
        return 0;
    }
}
