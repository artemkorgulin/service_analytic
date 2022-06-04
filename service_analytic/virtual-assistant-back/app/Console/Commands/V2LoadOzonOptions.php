<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\OzCategory;
use App\Services\V2\FeatureUpdater;
use App\Services\V2\OzonApi;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use function Swoole\Coroutine\run;

/**
 * Class V2LoadOzonOptions
 * Позволяет выгрузить из озона список значений всех характеристик-справочников
 * @package App\Console\Commands
 */
class V2LoadOzonOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_options {--max_coroutines=} {--last_sync_date=} {--feature_ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить значения характеристик категорий Озона. Если не указана дата последней '
    .'синхронизации используем текущую. Обновление будет только для характеристик дата последнего обновления, '
    .'которых, меньше или равно указанной или текущей. Таким образом команда может продолжать работу с того места, на котором '
    .'закончилась работа, если она завершилась по ошибке или была прервана. Если не указаны характеристики через запятую, обновляем по всем';

    /** @var OzonApi $ozonApiService */
    protected OzonApi $ozonApiService;

    /**
     * @var int Max coroutines count
     */
    protected int $maxCoroutines;

    /**
     * @var string
     */
    protected string $lastSyncDate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ozonApiService = new OzonApi(
            config('env.ozon_command_client_id'),
            config('env.ozon_command_api_key')
        );
    }

    protected function addCategoryTasks($categoriesChunk, $feature, $barCategory)
    {
        foreach ($categoriesChunk as $category) {
            go(function () use ($feature, $category, $barCategory) {
                $this->runUniqueFeatureUpdater($feature, $category, $barCategory);
            });
            $this->info(sprintf(
                "%s поставлена задача на получение значений по категории %s - %s - %s и характеристике %s - %s",
                date('Y-m-d H:i:s'),
                $category->id,
                $category->external_id,
                $category->name,
                $feature->id,
                $feature->name
            ));
        }
    }

    protected function initCommand()
    {
        if (!empty($this->option('last_sync_date'))
            && DateTime::createFromFormat('Y-m-d', $this->option('last_sync_date')) !== false) {
            $this->lastSyncDate = $this->option('last_sync_date');
        } else {
            $this->lastSyncDate = date('Y-m-d');
        }

        if (!empty($this->option('max_coroutines'))) {
            $this->maxCoroutines = (int) $this->option('max_coroutines');
        } else {
            $this->maxCoroutines = config('console.max_coroutines_load_feature_options');
        }

        $this->info(sprintf(
            '%s - Обновление значений %s категорий Озона. Дата последней синхронизации %s',
            date('Y-m-d H:i:s'),
            !empty($this->option('feature_ids')) ? 'по характеристикам с id '.$this->option('feature_ids') : 'по всем характеристикам',
            $this->lastSyncDate
        ));

        DB::table('oz_feature_to_option_load')->truncate();
    }

    protected function initBar($count): ProgressBar
    {
        $this->info(sprintf("Число найденных элементов для получения %s \n", $count));
        $bar = $this->output->createProgressBar($count);
        $bar->setFormat('debug');
        $this->info(date('Y-m-d H:i:s')." ".$bar->start()."\n");

        return $bar;
    }

    protected function finishCategoryOptionLoad($bar, $feature)
    {
        $this->info(date('Y-m-d H:i:s').' '.$bar->finish()."\n");
        $this->info(sprintf("Обновление значений характеристики %s по категориям успешно завершено\n",
            $feature->id));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->initCommand();

        // Обновляем значения характеристик не уникальные по всем категориям
        $featuresWithoutUnique = $this->getNotUniqueFeatures();

        if ($featuresWithoutUnique->count('id') > 0) {
            $bar = $this->initBar($featuresWithoutUnique->count('id'));
            $featuresWithoutUnique->chunk($this->maxCoroutines, function ($featuresChunk) use ($bar) {
                run(function () use ($featuresChunk, $bar) {
                    foreach ($featuresChunk as $feature) {
                        go(function () use ($feature, $bar) {
                            $this->runNotUniqueFeatureUpdater($feature);
                            $this->info(date('Y-m-d H:i:s') . " " . $bar->advance() . "\n");
                        });
                        $this->info(sprintf(
                            "%s поставлена задача на получение значений по характеристике %s\n",
                            date('Y-m-d H:i:s'),
                            $feature->id
                        ));
                    }
                });
            });

            $this->info(date('Y-m-d H:i:s')." ".$bar->finish()."\n");
        }

        // Обновляем значения характеристик уникальные для всех категорий
        $featuresUnique = $this->getUniqueFeatures()->cursor();

        if ($featuresUnique->count() > 0) {
            foreach ($featuresUnique as $feature) {
                $categories = $feature->categories();
                $barCategory = $this->initBar($categories->count('id'));
                $categories->chunk($this->maxCoroutines, function ($categoriesChunk) use ($feature, $barCategory) {
                    run(function () use ($categoriesChunk, $feature, $barCategory) {
                        $this->addCategoryTasks($categoriesChunk, $feature, $barCategory);
                    });
                });

                $this->finishCategoryOptionLoad($barCategory, $feature);
            }
        }

        // Обновляем значения характеристик уникальные для выборочных категорий
        $featuresUnique = $this->getUniqueFeatures(true)->cursor();

        if ($featuresUnique->count() > 0) {
            foreach ($featuresUnique as $feature) {
                $id = Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS[$feature->id];
                $categories = OzCategory::query()->whereIn('external_id', Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS[$feature->id]);
                $count = $categories->count();
                $barCustomCategories = $this->initBar($categories->count('id'));
                $categories->chunk($this->maxCoroutines, function ($categoriesChunk) use ($feature, $barCustomCategories) {
                    run(function () use ($categoriesChunk, $feature, $barCustomCategories) {
                        $this->addCategoryTasks($categoriesChunk, $feature, $barCustomCategories);
                    });
                });

                DB::table('oz_feature_to_option')
                    ->whereIn('feature_id', array_keys(Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS))
                    ->whereNotNull('category_id')
                    ->update(['category_id' => null]);

                $this->finishCategoryOptionLoad($barCustomCategories, $feature);
            }
        }

        $this->info(date('Y-m-d H:i:s') . " - Обновление значений по характеристикам категорий успешно завершено\n");

        return Command::SUCCESS;
    }

    /**
     * Get not unique category features
     *
     * @return Builder
     */
    private function getNotUniqueFeatures(): Builder
    {
        $inputFeatureIds = $this->getNotUniqueInputFeatureIds();
        $uniqueIds = array_merge(
            Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS,
            array_keys(Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS)
        );

        return Feature::query()
            ->where('is_reference', true)
            ->where(function ($subQuery) {
                $subQuery->where('oz_feature_to_option_last_sync_date', '<=', $this->lastSyncDate)
                    ->orWhereNull('oz_feature_to_option_last_sync_date');
            })->orderBy('id')
            ->whereNotIn('id', $uniqueIds)
            ->when(!empty($inputFeatureIds), function (Builder $query) use ($inputFeatureIds) {
                $query->whereIn('id', $inputFeatureIds);
            })
            ->when(empty($inputFeatureIds) && !empty($this->option('feature_ids')), function (Builder $query) {
                // Если среди указанных в параметре команды feature_ids нет не уникальных по категориям характеристик, возвращаем нулевой запрос
                $query->where('id', '=', 0);
            });
    }

    /**
     * @param false $withCustomCategoryUnique
     * @return Builder
     */
    private function getUniqueFeatures(bool $withCustomCategoryUnique = false): Builder
    {
        if ($withCustomCategoryUnique) {
            $inputFeatureIds = $this->getInputFeatureIdsForUniqueCustomCategories();
        } else {
            $inputFeatureIds = $this->getInputFeatureIdsForUniqueCategories();
        }

        return Feature::query()
            ->where('is_reference', true)
            ->where(function ($subQuery) {
                $subQuery->where('oz_feature_to_option_last_sync_date', '<=', $this->lastSyncDate)
                    ->orWhereNull('oz_feature_to_option_last_sync_date');
            })->orderBy('id')
            ->when(empty($inputFeatureIds) && !empty($this->option('feature_ids')), function (Builder $query) {
                // Если среди указанных в параметре команды feature_ids нет уникальных по категориям характеристик, возвращаем нулевой запрос
                $query->where('id', '=', 0);
            })
            ->when(!empty($inputFeatureIds), function (Builder $query) use ($inputFeatureIds) {
                $query->whereIn('id', $inputFeatureIds);
            })
            ->when(empty($inputFeatureIds) && $withCustomCategoryUnique === true, function (Builder $query) {
                $query->whereIn('id', array_keys(Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS));
            })
            ->when(empty($inputFeatureIds) && $withCustomCategoryUnique === false, function (Builder $query) {
                $query->whereIn('id', Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS);
            });
    }

    private function getInputFeatureIds(): array
    {
        return !empty($this->option('feature_ids')) ? explode(',', $this->option('feature_ids')) : [];
    }

    private function getNotUniqueInputFeatureIds(): array
    {
        $inputIds = $this->getInputFeatureIds();
        $resultIds = [];
        $customFeatureKeys = array_keys(Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS);

        foreach ($inputIds as $featureId) {
            if (!in_array($featureId, Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS) && !in_array($featureId, $customFeatureKeys)) {
                $resultIds[] = $featureId;
            }
        }

        return $resultIds;
    }

    private function getInputFeatureIdsForUniqueCategories(): array
    {
        $inputIds = $this->getInputFeatureIds();
        $resultIds = [];

        foreach ($inputIds as $featureId) {
            if (in_array($featureId, Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS)) {
                $resultIds[] = $featureId;
            }
        }

        return $resultIds;
    }

    private function getInputFeatureIdsForUniqueCustomCategories(): array
    {
        $inputIds = $this->getInputFeatureIds();
        $resultIds = [];
        $customFeatureIds = array_keys(Feature::UNIQUE_FOR_CUSTOM_CATEGORIES_FEATURE_IDS);

        foreach ($inputIds as $featureId) {
            if (in_array($featureId, $customFeatureIds)) {
                $resultIds[] = $featureId;
            }
        }

        return $resultIds;
    }

    /**
     * @param $feature
     */
    private function runNotUniqueFeatureUpdater($feature)
    {
        try {
            (new FeatureUpdater($this->ozonApiService, $feature))->handle();
        } catch (Exception $exception) {

            $this->error(sprintf('Произошла ошибка при обновлении значений характеристике - %s: %s',
                $feature->id, $exception->getMessage()));
        }
    }

    /**
     * @param $feature
     * @param $category
     * @param $barCategory
     */
    private function runUniqueFeatureUpdater($feature, $category, $barCategory)
    {
        try {
            (new FeatureUpdater($this->ozonApiService, $feature))->handleWithCategories($category, $barCategory, $this);
        } catch (Exception $exception) {
            report($exception);
            $this->error(sprintf('Произошла ошибка при обновлении значений характеристики - %s по категории %s: %s',
                $feature->id, $category->id, $exception->getMessage()));
        }
    }
}
