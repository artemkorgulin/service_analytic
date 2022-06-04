<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearHistoryProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:history 
                    {model : Полный путь до модели которую нужно очистить} 
                    {sku-array : Массив vendor_code соторые нужно оставить}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка таблиц от удалённых из отслеживания товаров';

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
        $model = $this->argument('model');
        $skuArray = $this->argument('sku-array');

        $products = $model::query()->cursor();

        foreach ($products as $product) {
            if (!in_array($product->vendor_code, $skuArray)) {
                $model::query()->where('vendor_code', '=', $product->vendor_code)->delete();
            }
        }

        return self::SUCCESS;
    }
}
