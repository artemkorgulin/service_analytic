<?php

namespace App\Console\Commands;

use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Console\Command;
use function Swoole\Coroutine\run;

class UpdateOptimizationForAllProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimization:update {--all= : для всех или только для нерассчитанных}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Данная команда пробегается по всем товарам и рассчитывает все степени оптимизации, нужна когда нужно обновить всем и массово';

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
        $maxCoroutines = 50;
        $models = [
            'App\Models\OzProduct' => 'App\Services\V2\OzonOptimizationService',
            'App\Models\WbProduct' => 'App\Services\V2\WbProductServiceUpdater'
        ];
        foreach ($models as $model => $service) {
            $this->line("Calculate optimization $model products");
            $model = app($model);
            if (!$this->option('all')) {
                $model = $model->whereNull('visibility_optimization')
                    ->orWhereNull('search_optimization')
                    ->orWhereNull('content_optimization')
                    ->orWhereNull('optimization');
            }
            if (!$model->count()) {
                $this->line("All products already calculated!");
                continue;
            }
            $model->chunk($maxCoroutines, function ($products) use ($service, $model) {
                run(function () use ($model, $service, $products) {
                    // Обновление оптимизации для товаров
                    foreach ($products as $product) {
                        go(function () use ($product, &$data, $service, $model) {
                            try {
                                $content = $service::calculateContentOptimization($product);
                                $search = $service::calculateSearchOptimization($product);
                                $visibility = $service::calculateVisibilityOptimization($product);
                                $averageOptimization = intval(($visibility + $search + $content) / 3);
                                $data = [
                                    'visibility_optimization' => $visibility,
                                    'search_optimization' => $search,
                                    'content_optimization' => $content,
                                    'optimization' => $averageOptimization,
                                ];
                                ModelHelper::transaction(function () use ($data, $model, $product) {
                                    $model->where('id', $product->id)->update($data);
                                });
                                $this->line("Product $product->id updated!");
                            } catch (\Exception $exception) {
                                report($exception);
                                $this->line("Product ID {$product->id}: " . $exception->getMessage());
                            }
                        });
                    }
                });
            });
        }
        return Command::SUCCESS;
    }
}
