<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductInWildberries;
use App\Models\WbProduct;
use Illuminate\Console\Command;

class WbSendProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:send-products {--id=*: id продуктов которые хотите отправить в Wildberries }';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить существующие и записанные продукты в Wildberries';

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
        $ids = $this->option('id') ?? [];

        if ($ids && !empty($ids)) {
            foreach ($ids as $id) {
                $id = (int) $id;
                $product = WbProduct::findOrFail($id);
                if ($product) {
                    $this->info("Отправил задачу на синхронизацию продукта \"$product->title\" с Wildberries id={$id}");
                    UpdateProductInWildberries::dispatch($product);
                } else {
                    $this->error("Продукт с id={$id} не обнаружен в нашей базе!");
                }
            }
        } else {
            $this->alert('Нет продуктов');
        }
    }
}
