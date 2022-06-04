<?php

namespace App\Console\Commands;

use App\Models\OzCategory;
use App\Services\V2\CategoryFeatureUpdater;
use App\Services\V2\OzonApi;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use function Swoole\Coroutine\run;

/**
 * Class V2LoadOzonCategoryFeatures
 * Позволяет выгрузить из озона список всех характеристик в каждой категории
 * @package App\Console\Commands
 */
class V2LoadOzonCategoryFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_features {--category_id=} {--max_coroutines=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить характеристики категорий Озона, если не указана ID характеристики будут обновлены все';

    /** @var OzonApi $ozonApiService */
    protected $ozonApiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->ozonApiService = new OzonApi(config('env.ozon_command_client_id'), config('env.ozon_command_api_key'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($categoryId = (int) $this->option('category_id')) {
            $this->info('Обновление характеристик категории ' . $categoryId);
            $category = OzCategory::find($categoryId);

            if (empty($category)) {
                $category = OzCategory::where('external_id', $categoryId)->first();
                if (!$category) {
                    $this->info('Категория не найдена');
                    return Command::FAILURE;
                }
            }

            $bar = $this->output->createProgressBar(1);
            $bar->setFormat('debug');
            $this->info(date('Y-m-d H:i:s') . " " . $bar->start() . "\n");

            try {
                (new CategoryFeatureUpdater($this->ozonApiService, $category))->loadFeatures();
            } catch (Exception $exception) {
                report($exception);
                $this->error('Произошла ошибка при обновлении характеристик категории ' . $categoryId . ': ' . $exception->getMessage());
                return Command::FAILURE;
            }

            $this->info(date('Y-m-d H:i:s') . " " . $bar->advance() . "\n");
            $this->info(date('Y-m-d H:i:s') . " " . $bar->finish() . "\n");
            $this->info('Обновление характеристик категорий успешно завершено');

            return Command::SUCCESS;
        }

        if (!empty($this->option('max_coroutines'))) {
            $maxCoroutines = (int) $this->option('max_coroutines');
        } else {
            $maxCoroutines = config('console.max_coroutines_load_features');
        }

        $this->info('Обновление характеристик категорий Озона');
        /** @var Builder $categories */
        $categories = OzCategory::query()->where('is_deleted', 0);

        if ($categories->count() < 1) {
            $this->info('Категорий не найдено');

            return Command::FAILURE;
        }

        $bar = $this->output->createProgressBar($categories->count());
        $bar->setFormat('debug');
        $this->info(date('Y-m-d H:i:s') . " " . $bar->start() . "\n");

        try {
            $categories->chunk($maxCoroutines, function ($categoriesChunk) use ($bar) {
                run(function () use ($categoriesChunk, $bar) {
                    foreach ($categoriesChunk as $category) {
                        go(function () use ($category, $bar) {
                            (new CategoryFeatureUpdater($this->ozonApiService, $category))->loadFeatures();
                            $this->info(date('Y-m-d H:i:s') . " " . $bar->advance() . "\n");
                        });
                    }
                });
            });
        } catch (Exception $exception) {
            report($exception);
            $this->error('Произошла ошибка при обновлении характеристик: ' . $exception->getMessage());
        }

        $this->info(date('Y-m-d H:i:s') . " " . $bar->finish() . "\n");
        $this->info('Обновление характеристик категорий успешно завершено');

        return Command::SUCCESS;
    }
}
