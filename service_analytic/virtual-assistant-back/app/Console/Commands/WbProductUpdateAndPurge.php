<?php

namespace App\Console\Commands;

use App\Models\WbProduct;
use Illuminate\Console\Command;

class WbProductUpdateAndPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:product-update-and-purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка и таблицы wb_products';

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
        WbProduct::select()->orderBy('id','ASC')->chunk(100, function ($products) {
            foreach ($products as $product) {
                $this->info("Обрабатываю {$product->title}");
                $product->title = trim($product->title).' ';
                $product->save();
                $product->title = trim($product->title);
                $product->save();
            }
        });
        return 0;
    }
}
