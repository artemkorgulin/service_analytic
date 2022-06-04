<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Models\ProductPositionHistory;
use App\Services\V2\ProductServiceUpdater;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class V2LoadProductsStats
 * Позволяет выгрузить из сервиса MPStats информацию о текущей позиции для всех товаров из БД
 * @package App\Console\Commands
 */
class V2LoadProductsStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_products_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет информацию о позиции товара';

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
        $this->info('Попытка обновления ранее необработанных товаров');

        //Товар считается необработанным, если для него есть запись
        //В таблице истории позиции с position = null
        $unhandledProductPosition = ProductPositionHistory::query()
            ->whereNull('position')
            ->groupBy('date')
            ->get();

        $bar = $this->output->createProgressBar(count($unhandledProductPosition));
        $bar->start();

        foreach ($unhandledProductPosition as $position) {
            if (isset($position->product->id)) {
                try {
                    $productServiceUpdate = new ProductServiceUpdater($position->product->id);
                    $productServiceUpdate->updateUnhandled($position);
                } catch (Exception $exception) {
                    report($exception);
                    $this->error('Произошла ошибка при обновлении товара id=' . $position->product->id . ': ' . $exception->getMessage());
                }
            }
            $bar->advance();
        }

        $bar->finish();

        $this->info('Обновление позиций товаров из MPStats');
        $products = OzProduct::all();//TODO убрать
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $product) {
            try {
                $productServiceUpdate = new ProductServiceUpdater($product->id);
                $productServiceUpdate->updateStats();
            } catch (Exception $exception) {
                report($exception);
                $this->error('Произошла ошибка при обновлении товара id=' . $product->id . ': ' . $exception->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info('Обновление позиций товаров из MPStats завершено');

        return 0;
    }
}
