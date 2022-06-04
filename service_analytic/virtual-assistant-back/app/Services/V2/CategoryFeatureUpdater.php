<?php


namespace App\Services\V2;

use App\Exceptions\Ozon\OzonServerException;
use App\Models\Feature;
use App\Models\OzCategory;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Support\Facades\DB;

/**
 * Класс для актуализации информации о характеристиках категории
 * Class CategoryFeatureUpdater
 * @package App\Services\V2
 */
class CategoryFeatureUpdater
{
    /**
     * Коллекция товаров
     * @var OzCategory
     */
    protected $category;

    /**
     * @var OzonApi
     */
    protected $ozonApiClient;


    /**
     * CategoryFeatureUpdater constructor.
     * @param OzonApi $ozonApiClient
     * @param OzCategory $category
     */
    public function __construct(OzonApi $ozonApiClient, OzCategory $category,)
    {
        $this->category = $category;
        $this->ozonApiClient = $ozonApiClient;
    }

    /**
     * Метод для обновления характеристик категории
     */
    public function loadFeatures(): void
    {
        $features = $this->getFeaturesAndPrepareQueryData();
        echo sprintf(
            "%s Характеристики по категории %s  получены с Озон\n", date('Y-m-d H:i:s'),
            $this->category->id
        );

        if (!empty($features)) {
            go(function () use ($features) {
                $this->deleteOldFeatures($features['featuresToInsert']);
                $this->createOrUpdateFeature($features);
                DB::table('oz_features')->whereIn('id', Feature::UNIQUE_FOR_CATEGORIES_FEATURE_IDS)
                    ->update(['is_unique_for_category' => 1]);

                echo sprintf(
                    "%s Характеристики по категории %s  сохранены в базе данных\n", date('Y-m-d H:i:s'),
                    $this->category->id
                );
            });
        }
    }

    /**
     * Удаляем лишние атрибуты (которых нет в озоне)
     *
     * @param $newFeatures
     * @return void
     */
    public function deleteOldFeatures(array $newFeatures)
    {
        $ids = collect($newFeatures)->pluck('id')->toArray();

        ModelHelper::transaction(function () use ($ids) {
            DB::table('oz_category_to_feature')->where('category_id', $this->category->id)
                ->whereNotIn('feature_id', $ids)->delete();
        });
    }

    /**
     * Добавить или обновить характеристику в БД
     *
     * @param array $features
     * @return void
     */
    protected function createOrUpdateFeature(array $features)
    {
        ModelHelper::transaction(function () use ($features) {
            // Массово вставим или обновим значения характеристики
            DB::table('oz_features')->upsert(
                $features['featuresToInsert'],
                'id',
                ['name', 'is_reference', 'is_required', 'is_collection', 'is_specialty', 'description', 'type', 'updated_at']
            );

            // Привязка категории к характеристике
            DB::table('oz_category_to_feature')->insertOrIgnore($features['attachData']);
        });
    }

    /**
     * Получение данных с Озон и формирование массива для вставки в БД
     *
     * @return array
     * @throws OzonServerException
     */
    protected function getFeaturesAndPrepareQueryData(): array
    {
        $result = [];
        $categoryFeaturesResponse = $this->ozonApiClient->repeat('getCategoryFeatureV3', [$this->category->external_id]);
        $categoryFeatures = $categoryFeaturesResponse['data']['result'][0]['attributes'] ?? false;
        $now = now();

        if (!$categoryFeatures) {
            return $result;
        }

        foreach ($categoryFeatures as $categoryFeature) {
            $result['attachData'][] = [
                'category_id' => $this->category->id,
                'feature_id' => $categoryFeature['id']
            ];
            $result['featuresToInsert'][] = [
                'id' => $categoryFeature['id'],
                'name' => $categoryFeature['name'],
                'is_reference' => $categoryFeature['dictionary_id'] > 0,
                'is_required' => $categoryFeature['is_required'],
                'is_collection' => $categoryFeature['is_collection'],
                'is_specialty' => mb_stripos($categoryFeature['name'], 'особенност') !== false,
                'description' => $categoryFeature['description'] ?? null,
                'type' => $categoryFeature['type'] ?? null,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        return $result;
    }
}
