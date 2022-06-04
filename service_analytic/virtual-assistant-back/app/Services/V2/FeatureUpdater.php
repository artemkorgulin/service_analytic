<?php

namespace App\Services\V2;

use App\Models\Feature;
use App\Models\OzCategory;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Класс для актуализации информации справочника значений по характеристикам
 * Class FeatureUpdater
 * @package App\Services\V2
 */
class FeatureUpdater
{
    /**
     * Характеристика
     * @var Feature
     */
    protected Feature $feature;

    /**
     * Связка характеристика категория
     * @var Model|null
     */
    protected $categoryFeature = null;

    /**
     * @var OzonApi
     */
    protected OzonApi $ozonApiClient;

    /**
     * @var int
     */
    protected int $lastValueId = 0;

    /**
     * @var OzCategory|null
     */
    protected ?OzCategory $firstCategory;

    /**
     * @var string
     */
    protected string $startTime;

    /**
     * FeatureUpdater constructor.
     *
     * @param OzonApi $ozonApiClient
     * @param Feature $feature
     */
    public function __construct(OzonApi $ozonApiClient, Feature $feature)
    {
        $this->feature = $feature;
        $this->ozonApiClient = $ozonApiClient;

        // Отключаем логирование и события на запросы в фасаде DB, иначе в цикле будет утекать память
        DB::connection()->unsetEventDispatcher();
        DB::disableQueryLog();
    }

    /**
     * Входная точка для обновления значений характеристик в категории
     * @throws Exception
     */
    public function handle(): void
    {
        if (!$this->initLoad()) {
            Log::channel('feature_load')->error("Неудачная инициализация при получении опций из Озон для id=".$this->feature->id);
            return;
        }

        if ($this->feature->is_unique_for_category === 1 || in_array($this->feature->id,
                Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS)) {
            return;
        }

        $this->runLoadAndInsert();
        $this->syncOptionsInDatabase();
        $this->finishLoad();
    }

    public function handleWithCategories(OzCategory $category, $barCategory, $consoleApp)
    {
        if (!$this->initLoad()) {
            Log::channel('feature_load')->error("Неудачная инициализация при получении опций из Озон для id=".$this->feature->id);
            return;
        }

        $this->categoryFeature = DB::table('oz_category_to_feature')->where([
            'feature_id' => $this->feature->id, 'category_id' => $category->id
        ])->first();
        $this->categoryFeature->old_count_values = $this->categoryFeature->count_values ?: 0;
        $this->categoryFeature->count_values = 0;
        $this->runLoadAndInsert($category);
        $this->syncOptionsInDatabase($category->id);
        $this->finishLoad($category->id);
        $consoleApp->info(date('Y-m-d H:i:s')." ".$barCategory->advance()."\n");
        $consoleApp->info(sprintf(
            "Завершено получение значений по категории %s - %s - %s и характеристике %s - %s\n",
            $category->id,
            $category->external_id,
            $category->name,
            $this->feature->id,
            $this->feature->name
        ));
    }

    /**
     * Подготовка к загрузке
     *
     * @return bool
     */
    private function initLoad(): bool
    {
        if (!$this->feature->is_reference) {
            return false;
        }

        $this->firstCategory = $this->feature->categories()->where('is_deleted', false)->first();

        if (!$this->firstCategory) {
            Log::channel('feature_load')->error(sprintf(
                'Характеристика %s - %s не содержит категорию. Завершение загрузки по ней.',
                $this->feature->id,
                $this->feature->name
            ));
            return false;
        }

        $this->startTime = microtime(true);
        $this->feature->old_count_values = $this->feature->count_values ?: 0;
        $this->feature->count_values = 0;

        return true;
    }

    /**
     * Получаем данные с Озон
     *
     * @return false|array
     */
    private function getFeatureOptionInOzon($category = null): bool|array
    {
        if (empty($category)) {
            $category = $this->firstCategory;
        }

        try {
            $featureOptionsResponse = $this->ozonApiClient->ozonRepeat(
                'getCategoryFeatureOptions',
                $category->external_id, $this->feature->id, $this->lastValueId
            );
        } catch (Exception $exception) {
            report($exception);
            Log::channel('feature_load')->error(sprintf(
                'Ошибка при получении опций из Озон для %s - %s по категории %s - %s %s',
                $this->feature->id,
                $this->feature->name,
                $category->external_id,
                $category->name,
                $exception->getMessage()
            ));

            return false;
        }

        if (empty($featureOptionsResponse['data']) || empty($featureOptionsResponse['data']['result'])) {
            return false;
        }

        return $featureOptionsResponse['data'];
    }

    /**
     * Обновляем базу данных
     * @param $featureOptionsResponse
     * @throws Exception
     */
    private function insertToDatabase($featureOptionsResponse, ?int $categoryId = null)
    {
        $prepareQueryData = $this->prepareQueryData($featureOptionsResponse, $categoryId);
        ModelHelper::transaction(function () use ($prepareQueryData) {
            // Массово вставим или обновим значения характеристики
            DB::table('oz_feature_options')->upsert(
                $prepareQueryData['optionsToInsert'],
                'id',
                ['updated_at', 'value']
            );

            // Вставляем опции полученные данные с озона, чтобы потом искать новые и старые опции
            DB::table('oz_feature_to_option_load')->upsert($prepareQueryData['data'],
                ['category_id', 'feature_id', 'option_id'], ['value']);
        });
    }

    private function runLoadAndInsert($category = null)
    {
        $hasNext = true;
        do {
            $featureOptionsResponse = $this->getFeatureOptionInOzon($category);

            if (!isset($featureOptionsResponse['has_next']) || empty($featureOptionsResponse['has_next'])) {
                $hasNext = false;
            }

            if (empty($featureOptionsResponse) || empty($featureOptionsResponse['result'])) {
                continue;
            }

            $this->insertToDatabase($featureOptionsResponse, optional($category)->id);
        } while ($hasNext);
    }

    /**
     * Вставляем новые связи значений с характеристиками и удаляем устаревшие значения из базы данных
     * @throws Exception
     */
    private function syncOptionsInDatabase(?int $categoryId = null)
    {
        $categoryQuery = empty($categoryId) ? 'AND fol.category_id IS NULL' : 'AND fol.category_id = '.$categoryId;
        $categoryInsertColumnQuery = empty($categoryId) ? 'NULL AS category_id' : $categoryId.' AS category_id';

        // Вставляем новые связи значений с характеристикой
        ModelHelper::transaction(function () use ($categoryId, $categoryQuery, $categoryInsertColumnQuery) {
            DB::insert(<<<SQL
                INSERT IGNORE INTO `oz_feature_to_option` (`option_id`, `feature_id`, `category_id`)
                    SELECT fol.`option_id`, fol.`feature_id`, {$categoryInsertColumnQuery}
                    FROM `oz_feature_to_option_load` fol
                    LEFT JOIN `oz_feature_to_option` AS fto ON fol.`feature_id` = fto.`feature_id` AND fol.`option_id` = fto.`option_id`
                    WHERE fol.`feature_id` = {$this->feature->id} {$categoryQuery} AND fto.`option_id` IS NULL AND fol.`option_id` IS NOT NULL
            SQL
            );
        });

        // Удаляем связи значений с характеристикой, которых больше нет в Озон
        $categoryQuery = empty($categoryId) ? 'AND fto.category_id IS NULL' : 'AND fto.category_id = '.$categoryId;
        ModelHelper::transaction(function () use ($categoryQuery) {
            DB::delete(<<<SQL
                DELETE fto FROM `oz_feature_to_option` AS fto
                LEFT JOIN `oz_feature_to_option_load` fol ON fto.`feature_id` = fol.`feature_id` AND fto.`option_id` = fol.`option_id`
                WHERE fto.`feature_id` = {$this->feature->id} {$categoryQuery} AND fto.`option_id` IS NOT NULL AND fol.`option_id` IS NULL
            SQL
            );
        });

        // Удаляем связи значений характеристики, которых больше нет в Озон, с товарами
        ModelHelper::transaction(function () use ($categoryId) {
            if (empty($categoryId)) {
                DB::delete(<<<SQL
                    DELETE opf FROM `oz_products_features` AS opf
                    LEFT JOIN `oz_feature_to_option_load` fol ON opf.`option_id` = fol.`option_id` AND opf.`feature_id` = fol.`feature_id`
                    WHERE opf.`feature_id` = {$this->feature->id} AND opf.option_id IS NOT NULL AND fol.option_id IS NULL
                SQL
                );
            } else {
                DB::delete(<<<SQL
                    DELETE opf FROM `oz_products_features` AS opf
                    LEFT JOIN `oz_products` AS p ON opf.product_id = p.id
                    LEFT JOIN `oz_feature_to_option_load` fol ON opf.`option_id` = fol.`option_id` AND opf.`feature_id` = fol.`feature_id`
                    WHERE opf.`feature_id` = {$this->feature->id} AND p.category_id = {$categoryId} AND opf.option_id IS NOT NULL AND fol.option_id IS NULL
                SQL
                );
            }
        });
    }

    /**
     * Подготавливаем полученные данные для вставки в базу
     *
     * @param $featureOptionsResponse
     * @return array
     */
    private function prepareQueryData($featureOptionsResponse, ?int $categoryId = null): array
    {
        $featureOptions = $featureOptionsResponse['result'];
        $currentDateTime = now();
        $result = [];

        if (!empty($categoryId) && !empty($this->categoryFeature)) {
            $this->categoryFeature->count_values += count($featureOptions);
        } else {
            $this->feature->count_values += count($featureOptions);
        }

        foreach ($featureOptions as $featureOption) {
            $result['data'][] = [
                'option_id' => $featureOption['id'],
                'value' => $featureOption['value'],
                'feature_id' => $this->feature->id,
                'category_id' => $categoryId
            ];
            $result['optionsToInsert'][] = [
                'id' => $featureOption['id'],
                'value' => $featureOption['value'],
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ];
            $this->lastValueId = $featureOption['id'];
        }

        return $result;
    }

    /**
     * Окончание загрузки
     */
    private function finishLoad(?int $categoryId = null)
    {
        // Обновляем дату последней синхронизации для характеристики и чистим временную табличку от данных по текущей характеристике и категории
        if (empty($categoryId)) {
            $this->feature->oz_feature_to_option_last_sync_date = date('Y-m-d');
            $this->feature->updated_at = now();
            $this->feature->save();
            DB::table('oz_feature_to_option_load')->where('feature_id', '=', $this->feature->id)
                ->whereNull('category_id')->delete();
            $time = (microtime(true) - $this->startTime);
            Log::channel('feature_load')->info("Выгрузились опции из Озона time: ".$time);
            Log::channel('feature_load')->debug("Вставка новых опций time: ".$time);
            Log::channel('feature_load')->info('Время обновления характеристики id='.$this->feature->id.': '.$time);

            return;
        }

        DB::table('oz_category_to_feature')->where([
            'feature_id' => $this->feature->id, 'category_id' => $categoryId
        ])->update([
            'oz_feature_to_option_last_sync_date' => date('Y-m-d'),
            'count_values' => $this->categoryFeature->count_values,
            'old_count_values' => $this->categoryFeature->old_count_values
        ]);
        DB::table('oz_feature_to_option_load')->where([
            'feature_id' => $this->feature->id, 'category_id' => $categoryId
        ])->delete();
        $time = (microtime(true) - $this->startTime);
        Log::channel('feature_load')->info("Выгрузились опции из Озона time: ".$time);
        Log::channel('feature_load')->debug("Вставка новых опций time: ".$time);
        Log::channel('feature_load')->info(sprintf(
            'Время обновления по характеристики id=%s и по категории id=%s : %s',
            $this->feature->id,
            $categoryId,
            $time
        ));
    }
}
