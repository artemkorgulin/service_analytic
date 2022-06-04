<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\OzProduct;
use Illuminate\Console\Command;

class OzonGetProductBrand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:get-products-brands';


    // Бренд продукта
    protected const PRODUCT_BRAND_OZON_ID = 85;


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получить все бренды по товарам Ozon';

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
        foreach (OzProduct::all() as $product) {
            $feature = Feature::find(self::PRODUCT_BRAND_OZON_ID);
            $brand = $product->featuresValues()->firstWhere('oz_products_features.feature_id',
                    $feature->id)->value ?? null;
            $product->brand = $brand;
            $product->save();
            $this->info('Записываю продукт '.$product->name."\n");
        }
        return 0;
    }
}
