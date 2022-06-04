<?php

namespace App\Models;

use App\Constants\OzonFeaturesConstants;
use App\Constants\References\ProductStatusesConstants;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class Product
 *
 * @property integer $id
 * @property integer $external_id id товара в озоне
 * @property string $url
 * @property string $sku_fbo SKU FBO
 * @property string $sku_fbs SKU FBS
 * @property string|null $offer_id
 * @property string $name
 * @property integer $category_id
 * @property float $price
 * @property integer $count_photos кол-во фото
 * @property float $rating
 * @property integer $count_reviews кол-во отзывов
 * @property integer|null $web_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $photo_url
 * @property float|null $recommended_price
 * @property integer $is_for_sale
 * @property integer $status_id
 * @property integer $card_updated
 * @property integer $characteristics_updated
 * @property integer $position_updated
 * @property string|null $characteristics_updated_at
 * @property string|null $mpstat_updated_at
 * @property integer $show_success_alert
 * @property string $old_price старая цена
 * @property integer $quantity_fbo Наличие FBO
 * @property integer $quantity_fbs Наличие FBS
 * @property integer $is_test Тестовый товар
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductChangeHistory[] $changeHistory
 * @property-read int|null $change_history_count
 * @property-read int|null $feature_options_triggers_count
 * @property-read int|null $feature_triggers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OzProductFeature[] `$featuresValues
 * @property-read int|null $features_values_count
 * @property-read string $actual_name
 * @property-read array $characteristics
 * @property-read mixed $characteristics_count
 * @property-read mixed $error_characteristics
 * @property-read mixed $has_removed_from_sale_triggers
 * @property-read mixed $min_photos_trigger
 * @property-read mixed $min_reviews_trigger
 * @property-read mixed $new_features_options_trigger
 * @property-read mixed $new_features_trigger
 * @property-read mixed $oldest_updated_date
 * @property-read mixed $position
 * @property-read mixed $position_trigger
 * @property-read mixed $positions
 * @property-read bool|null $price_recommendation_higher
 * @property-read mixed $recomendations
 * @property-read array $recomendations_pdf
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductPositionHistory[] $latestPositionHistory
 * @property-read int|null $latest_position_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TriggerChangePhotos[] $photoTriggers
 * @property-read int|null $photo_triggers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductPositionHistory[] $positionHistory
 * @property-read int|null $position_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductPositionHistoryGraph[] $positionHistoryGraph
 * @property-read int|null $position_history_graph_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TriggerRemoveFromSale[] $removeFromSaleTriggers
 * @property-read int|null $remove_from_sale_triggers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TriggerChangeReviews[] $reviewTriggers
 * @property-read int|null $review_triggers_count
 * @property-read \App\Models\OzProductStatus $status
 * @property-read \App\Models\WebCategory|null $webCategory
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCardUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCharacteristicsUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCharacteristicsUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCountPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCountReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsAdvertised($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsForSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereMpstatUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePhotoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePositionUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereRecommendedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShowSuccessAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereWebCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class OzProduct extends Model
{
    use SoftDeletes;

    protected  $primaryKey = 'id';

    // @TODO Cut this constant to feature repository
    public const PRODUCT_DESCRIPTION_FEATURE_ID = 4191;

    /**
     * Константы для хранения
     */

    // Описание
    // 3D изображения
    public const PRODUCT_3D_IMAGE_FEATURE_ID = 4080;

    // изображения
    public const PRODUCT_IMAGE_FEATURE_ID = 4195;

    // Просто ролики с Youtube
    public const PRODUCT_YOUTUBE_FEATURE_ID = 4074;

    //Пример цвета
    public const COLOR_IMAGE_ID = 10098;
    // Игнорируемые характеристики по Ozon id
    // Нужно для того, чтобы не выводить по нескольку раз описаниние, картинки и т.д.
    //
    public const IGNORED_CHARACRESTICS_OZON_IDS = [
        307047, 307048, 329984,
    ];

    public const JOIN_CARD_CHARACRESTICS_IDS = [8292, 10289];

    // Бренд продукта
    public const PRODUCT_BRAND_OZON_ID = 85;

    protected $table = 'oz_products';

    protected $fillable = [
        'user_id', 'account_id', 'external_id', 'url', 'sku_fbo', 'sku_fbs', 'barcode', 'offer_id', 'name', 'brand', 'category_id',
        'price', 'min_ozon_price', 'vat', 'volume_weight', 'marketing_price', 'buybox_price',
        'premium_price', 'dimension_unit', 'depth', 'height', 'width', 'weight_unit',
        'weight', 'count_photos', 'rating', 'count_reviews', 'web_category_id', 'created_at',
        'updated_at', 'deleted_at', 'photo_url', 'recommended_price', 'is_for_sale', 'status_id',
        'card_updated', 'characteristics_updated', 'position_updated', 'optimization',
        'characteristics_updated_at', 'mpstat_updated_at', 'show_success_alert', 'old_price', 'is_test',
        'images', 'images360', 'color_image', 'quantity_fbo', 'quantity_fbs'
    ];

    protected $casts = [
        'images' => 'array',
        'images360' => 'array',
    ];


    /**
     * Find one product from current user
     *
     * @param $id
     * @return mixed
     */
    public static function findWithCurrentUser($id)
    {
        return self::currentUser()->find($id);
    }

    /**
     * Получение товара текущего пользователя
     * Внимание! Работает ТОЛЬКО для http (не для cli)
     * @return mixed
     * @throws \Exception
     */
    public static function currentUser()
    {
        return self::where('oz_products.user_id', '=', UserService::getUserId());
    }

    /**
     * Find one product from current user
     *
     * @param $id
     * @return mixed
     */
    public static function findWithCurrentAccount($id)
    {
        return self::currentAccount()->find($id);
    }

    /**
     * Get products of current user
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccount($query)
    {
        $userId = UserService::getUserId();
        $accountId = UserService::getAccountId();
        return $query->join('oz_product_user', 'oz_product_user.external_id', '=', 'oz_products.external_id')->where([
            'oz_product_user.user_id' => $userId,
            'oz_product_user.account_id' => $accountId
        ])->whereNull('oz_product_user.deleted_at');
    }

    /**
     * Get products of current user
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccountByParams($query, int $userId, int $accountId)
    {
        return $query->join('oz_product_user', 'oz_product_user.external_id', '=', 'oz_products.external_id')->where([
            'oz_product_user.user_id' => $userId,
            'oz_product_user.account_id' => $accountId
        ])->whereNull('oz_product_user.deleted_at');
    }


    /**
     * Get products of current user by params
     * @param $userId
     * @param $accountId
     * @return mixed
     */
    public function scopeCurrentUserAndAccountByParam($query, $userId, $accountId)
    {
        return $query->join('oz_product_user', 'oz_product_user.external_id', '=', 'oz_products.external_id')->where([
            'oz_product_user.user_id' => $userId,
            'oz_product_user.account_id' => $accountId
        ])->whereNull('oz_product_user.deleted_at');
    }

    /**
     * Get products of current user
     * @return mixed
     * @throws \Exception
     */
    public function scopeCurrentUserAndAccountWithNotActive($query)
    {
        $userId = UserService::getUserId();
        $accountId = UserService::getAccountId();
        return $query->join('oz_product_user', 'oz_product_user.external_id2', '=', 'oz_products.external_id')->where([
            'oz_product_user.user_id2' => $userId,
            'oz_product_user.account_id2' => $accountId
        ]);
    }

    /**
     * Получение товара текущего пользователя
     * Внимание! Работает ТОЛЬКО для http (не для cli)
     * @return mixed
     * @throws \Exception
     */
    public static function currentAccount()
    {
        if (app()->runningInConsole() === true) {
            throw new \Exception('Функция currentUser не работает из командной строки!', 500);
        }
        $account_id = UserService::getAccountId();
        return self::where('account_id', $account_id);
    }

    /**
     * Статус товара
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OzProductStatus::class, 'status_id');
    }

    /**
     * Категория
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OzCategory::class, 'category_id');
    }

    /**
     * Web категория
     *
     * @return BelongsTo
     */
    public function webCategory(): BelongsTo
    {
        return $this->belongsTo(WebCategory::class, 'web_category_id');
    }

    /**
     * Последняя позиция в истории
     *
     * @return HasMany
     */
    public function latestPositionHistory(): HasMany
    {
        return $this->hasMany(ProductPositionHistory::class, 'product_id')->latest();
    }

    /**
     * История изменений товара
     *
     * @return HasMany
     */
    public function changeHistory(): HasMany
    {
        return $this->hasMany(ProductChangeHistory::class, 'product_id');
    }

    /**
     * Get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        $userId = UserService::getUserId();
        return $query->join('oz_product_user AS oz_product_user1', 'oz_product_user1.external_id', '=', 'oz_products.external_id')->where([
            'oz_product_user1.user_id' => $userId
        ])->whereNull('oz_product_user1.deleted_at');
    }

    /**
     * Get products from current account
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query): mixed
    {
        $accountId = UserService::getAccountId();
        return $query->join('oz_product_user AS oz_product_user2', 'oz_product_user2.external_id', '=', 'oz_products.external_id')->where([
            'oz_product_user2.account_id' => $accountId,
        ])->whereNull('oz_product_user2.deleted_at');
    }

    /**
     * Получить скидку по товару
     * @return int|string
     */
    public function getDiscountAttribute()
    {
        $discount = 0;
        if ($this->price && $this->old_price && ($this->price < $this->old_price)) {
            $discount = number_format(100 - $this->price / ($this->old_price / 100), 2, '.', '');
        }
        return $discount;
    }

    /**
     * Позиция товара
     */
    public function getPositionAttribute()
    {
        return $this->positionHistory->count() ?
            ($this->positionHistory->whereNotNull('position')->sortBy([`date`, 'updated_at'])->last()->position ??
                null) :
            null;
    }

    /**
     * Количество заполненных характеристик товара
     */
    public function getCharacteristicsCountAttribute()
    {
        return $this->featuresValues->groupBy('feature_id')->count() . ' из ' . $this->category->features->count();
    }

    /**
     * Рейтинг
     * @param $value
     * @return float
     */
    public function getRatingAttribute($value)
    {
        return round($value, 2);
    }


    // ------------------ Getters section ---------------------- //

    /**
     * Триггер снятия с продажи
     */
    public function getHasRemovedFromSaleTriggersAttribute()
    {
        return (bool)$this->removeFromSaleTriggers()->where('is_shown', false)->count();
    }

    /**
     * Триггеры изменения характеристик
     *
     * @return HasMany
     */
    public function removeFromSaleTriggers(): HasMany
    {
        return $this->hasMany(TriggerRemoveFromSale::class, 'product_id');
    }

    /**
     * Информация о том, является ли текущая цена выше рекомендуемой
     * @return bool|null
     */
    public function getPriceRecommendationHigherAttribute()
    {
        if (!$this->recommended_price || $this->recommended_price == $this->price) {
            return null;
        }

        return $this->price > $this->recommended_price;
    }

    /**
     * Триггер изменения позиции
     */
    public function getPositionTriggerAttribute()
    {
        $position_trigger = $this->positionHistory->where('is_trigger', true)->first();

        if (!$position_trigger) {
            return 0;
        }

        $previous_position_model = $this->positionHistory->where('is_trigger', false)->last();

        $previous_position = $previous_position_model ? $previous_position_model->position : 0;
        $result = $position_trigger->position - $previous_position;
        $position_trigger->is_trigger = false;
        $position_trigger->save();
        return $result;
    }

    /**
     * Триггер изменения обзоров
     */
    public function getMinReviewsTriggerAttribute()
    {
        return (bool)$this->reviewTriggers->count();
    }

    /**
     * Триггер изменения фотографий
     */
    public function getMinPhotosTriggerAttribute()
    {
        return (bool)$this->photoTriggers->count();
    }

    /**
     * Получить самую старую дату обновления товара, используеющуюся при формировании записи о товаре
     */
    public function getOldestUpdatedDateAttribute()
    {
        $dates = [Carbon::parse($this->updated_at)->timestamp];

        /**
         * @var ProductPositionHistory $last_position
         */
        $last_position = $this->positionHistory->last();
        if ($last_position) {
            $dates[] = Carbon::parse($last_position->updated_at)->timestamp;
        }

        /**
         * @var $last_web_category WebCategoryHistory
         */
        $last_web_category = $this->webCategory ? $this->webCategory->lastHistory : null;
        if ($last_web_category) {
            $dates[] = Carbon::parse($this->webCategory->lastHistory->updated_at)->timestamp;
        }

        return Carbon::createFromTimestamp(min($dates))->toISOString();
    }

    /**
     * Получение информации по триггерам для списка товаров
     */
    public function getTriggersForList()
    {
        $hasReviewTrigger = $this->minReviewsTrigger;
        $hasRemovedFromSaleTriggers = $this->hasRemovedFromSaleTriggers;
        $position_trigger = $this->positionHistory->where('is_trigger', true)->first();
        $hasFeatureTriggers = (bool)$this->featureTriggers()->where('is_shown', false)->count();

        if ($position_trigger) {
            $previous_position_model = $this->positionHistory->where('is_trigger', false)->last();
            $previous_position = $previous_position_model ? $previous_position_model->position : 0;
            $position = $previous_position - $position_trigger->position;
        } else {
            $position = 0;
        }

        return [
            'characteristics' => $hasFeatureTriggers,
            'position' => $position,
            'reviews' => $hasReviewTrigger,
            'removedFromSale' => $hasRemovedFromSaleTriggers,
        ];
    }

    /**
     * Получение характеристик товара для детализации
     *
     * @return array
     */
    public function getCharacteristicsAttribute(): array
    {
        $result = [];
        foreach ($this->featuresValues as $featureValue) {
            if (in_array($featureValue->id, self::IGNORED_CHARACRESTICS_OZON_IDS)) {
                continue;
            }
            // See task SE-930 must change structure must $featureValue->feature_id not $featureValue->id!
            if (!isset($result[$featureValue->feature_id])) {
                $result[$featureValue->feature_id] = [
                    'id' => $featureValue->feature_id,
                    'name' => $featureValue->feature->name,
                    'value' => '',
                    'options' => [],
                    'is_required' => $featureValue->feature->is_required,
                    'is_collection' => $featureValue->feature->is_collection,
                    'is_reference' => $featureValue->feature->is_reference,
                    'is_specialty' => $featureValue->feature->is_specialty,
                    'description' => $featureValue->feature->description,
                    'type' => $featureValue->feature->type,
                ];
            }

            if (!$featureValue->feature->is_reference) {
                $result[$featureValue->feature_id]['value'] = $featureValue->value;
                if ($featureValue->feature->type === 'Integer') {
                    $result[$featureValue->feature_id]['value'] = (int)$featureValue->value;
                }
            } else {
                if ($option = $featureValue->option()->first()) {

                    $result[$featureValue->feature_id]['selected_options'][] = [
                        'id' => $option->id,
                        'value' => ($featureValue->feature->type === 'Integer') ? (int)$option->value : $option->value,
                        'popularity' => $option->popularity,
                    ];

                    if ($featureValue->feature->is_collection) {
                        // See task SE-918
                        $result[$featureValue->feature_id]['chosen_count']
                            = count($result[$featureValue->feature_id]['options'] ?? []);
                        $result[$featureValue->feature_id]['count'] = $featureValue->feature->count_values;
                    }
                }
            }


        }

        foreach ($result as $opId => $r) {
            if (isset($r['selected_options'])) {
                $selectedOptionIds = array_map(function ($option) {
                    return $option['id'];
                }, $r['selected_options']);
            } else {
                $selectedOptionIds = [];
            }
            $feature = Feature::find($opId);
            if ($feature) {
                foreach ($feature->options($this->category_id)->select([
                    'oz_feature_options.id AS id', 'oz_feature_options.value AS value', 'popularity'
                ])->whereNotIn('oz_feature_options.id', $selectedOptionIds)
                             ->orderBy('popularity', 'DESC')
                             ->take(20)->get() as $i) {
                    $result[$opId]['options'][] = [
                        'id' => $i->id,
                        'value' => $i->value,
                        'popularity' => $i->popularity,
                    ];
                }

                if (isset($result[$opId]['selected_options']) && isset($result[$opId]['options'])) {
                    $result[$opId]['options'] = array_merge($result[$opId]['selected_options'], $result[$opId]['options']);
                }
            }
        }

        // если на модерации, то заменим значения на те, что в таблице изменений
        $isNotFirstIteration = [];
        $moderationStatusId = OzProductStatus::where('code', ProductStatusesConstants::MODERATION_CODE)->first()->id;
        if ($this->status_id === $moderationStatusId && $this->changeHistory->count()) {
            /** @var ProductChangeHistory $lastHistory */
            $lastHistory = $this->changeHistory->last();
            $changesFeatures = $lastHistory->changedFeatures;
            /** @var ProductFeatureHistory $changedFeature */
            foreach ($changesFeatures as $changedFeature) {
                /** @var Feature $feature */
                if ($feature = Feature::find($changedFeature->feature_id)) {
                    if (!isset($result[$feature->id])
                        || !in_array($feature->id, $isNotFirstIteration)
                    ) {
                        $result[$feature->id] = [
                            'id' => $feature->id,
                            'name' => $feature->name,
                            'value' => '',
                            'options' => [],
                            'is_required' => $feature->is_required,
                            'is_collection' => $feature->is_collection,
                            'is_reference' => $feature->is_reference,
                            'is_specialty' => $feature->is_specialty,
                            'description' => $feature->description,
                            'type' => $feature->type,
                        ];
                    }
                    if (!$feature->is_reference) {
                        $result[$feature->id]['value'] = $changedFeature->value;
                    } else {
                        /** @var Option $option */
                        if ($option = Option::find($changedFeature->value)) {
                            $result[$feature->id]['selected_options'][] = [
                                'id' => $option->id,
                                'value' => $option->value,
                                'popularity' => $option->popularity,
                            ];

                            $result[$feature->id]['options'] = [];

                            $feature->options()
                                ->select(['oz_feature_options.id AS id', 'oz_feature_options.value AS value', 'popularity'])
                                ->orderBy('popularity', 'DESC')
                                ->take(20)->get()
                                ->map(function ($item) use (&$result, $feature) {
                                    $result[$feature->id]['options'][] = [
                                        'id' => $item->id,
                                        'value' => $item->value,
                                        'popularity' => $item->popularity,
                                    ];
                                });

                            $result[$feature->id]['options'][] = [
                                'id' => $option->id,
                                'value' => $option->value,
                                'popularity' => $option->popularity,
                            ];

                            if ($changedFeature->feature->is_collection) {
                                // See task SE-918
                                $result[$feature->id]['chosen_count']
                                    = count($result[$feature->id]['options'] ?? []);
                                $result[$feature->id]['count'] = $changedFeature->feature->count_values;
                            }
                        }
                    }
                }
                $isNotFirstIteration[] = $feature->id;
            }
        }

        // удалим значения, которые не нужно отображать на детальке
        foreach ($result as $featureId => $feature) {
            if (isset($feature['id']) && in_array($feature['id'], OzonFeaturesConstants::NOT_FOR_SHOW_ON_DETAIL)) {
                unset($result[$featureId]);
            }
        }

        return $result;
    }

    /**
     * Получение характеристик с ошибками
     */
    public function getErrorCharacteristicsAttribute()
    {
        $result = [];
        if ($this->changeHistory->count()) {
            $lastHistory = $this->changeHistory->last();
            $errorFeatures = $lastHistory->errorFeatures;
            foreach ($errorFeatures as $errorFeature) {
                /** @var Feature $feature */
                if ($feature = $errorFeature->feature()->first()) {
                    $result[$feature->id] = [
                        'id' => $feature->id,
                        'name' => $feature->name,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Получить актуальное название.
     * В случае, если товар на модерации, то возвращаем то имя, которое в таблице истории изменений
     *
     * @return string
     */
    public function getActualNameAttribute(): string
    {
        $name = $this->name;
        $moderationStatusId = OzProductStatus::where('code', ProductStatusesConstants::MODERATION_CODE)->first()->id;
        if ($this->status_id === $moderationStatusId && $this->changeHistory->count()) {
            /** @var ProductChangeHistory $lastHistory */
            $lastHistory = $this->changeHistory->last();
            $name = $lastHistory->name;
        }

        return $name;
    }

    /**
     * Получение истории позиции товара для построения графика в детализации
     */
    public function getPositionsAttribute()
    {
        $from = $request->from ?? Carbon::create('now')->subDays(30);
        $to = $request->to ?? Carbon::create('now');

        $categories = $this->positionHistoryGraph()
            ->pluck('category')
            ->unique()
            ->toArray();

        $response = [];

        $positionHistories = $this->positionHistoryGraph()
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<=', $to->toDateString())
            ->first();


        if (!$positionHistories) {
            return null;
        }

        foreach ($categories as $category) {
            for ($date = clone $from; $date->lessThanOrEqualTo($to); $date->addDay()) {
                $positionHistory = $positionHistories
                    ->where('date', '=', $date->toDateString())
                    ->where('category', '=', $category)
                    ->first();
                $position = $positionHistory->position ?? null;
                $response[$category][] = ['date' => $date->toDateString(), 'position' => $position];
            }
        }

        return $response;
    }

    /**
     * История позиции товара для блока с динамикой
     *
     * @return HasMany
     */
    public function positionHistoryGraph(): HasMany
    {
        return $this->hasMany(ProductPositionHistoryGraph::class, 'product_id');
    }

    /**
     * Получение рекомендация для продвижения товара (для отображения в пда
     *
     * @return array
     */
    public function getRecomendationsPdfAttribute()
    {
        $result = $this->recomendations;
        foreach ($result as &$block) {
            if ($block['header'] === 'Заголовок') {
                $block['text'] = 'Советуем оптимизировать заголовок в сервисе «Виртуальный помощник»';
            }
        }
        unset ($block);

        return $result;
    }

    /**
     * Получение рекомендация для продвижения товара
     * Константы из тз 2 релиза, UC03 пункт 1.4
     */
    public function getRecomendationsAttribute(): array
    {
        $rates_count = (int)((4.99 * $this->count_reviews - $this->rating * $this->count_reviews) / (5 - 4.99));
        if ($rates_count === 0) {
            $rates_count = 1;
        }

        $result = [
            [
                'header' => 'Рейтинг',
                'text' => $this->rating == 5 ? 'Рейтинг товара максимальный!' : 'Для улучшения рейтинга товара до «5» необходимо еще ' . $rates_count . ' ' . trans_choice('messages.reviews',
                        $rates_count) . ' с оценкой «5»'
            ],
            [
                'header' => 'Заголовок',
                'text' => 'Советуем оптимизировать заголовок в сервисе',
                'link' => config('env.front_app_url') . '/step-1'
            ],
        ];

        if ($this->webCategory) {
            /**
             * @var WebCategoryHistory $lastParsedWebCategory
             */
            $lastParsedWebCategory = $this->webCategory->history->last();
            if ($lastParsedWebCategory) {

                if ($lastParsedWebCategory->min_reviews > $this->count_reviews) {
                    $result[] = [
                        'header' => 'Отзывы',
                        'text' => 'В карточке этого товара должно быть не менее ' . $lastParsedWebCategory->min_reviews . ' отзывов (сейчас ' . $this->count_reviews . ')'
                    ];
                }

                if ($lastParsedWebCategory->min_photos > $this->count_photos) {
                    $result[] = [
                        'header' => 'Изображения',
                        'text' => 'В карточке этого товара должно быть не менее ' . $lastParsedWebCategory->min_photos . ' фото (сейчас ' . $this->count_photos . ')'
                    ];
                }

                if ($this->recommended_price) {
                    $result[] = [
                        'header' => 'Цена',
                        'text' => 'Средняя цена в ТОП-36 категории вашего товара ' . number_format($lastParsedWebCategory->average_price,
                                2) . '. OZON рекомендует установить вам цену равную ' . number_format($this->recommended_price,
                                2) . '. Текущая цена товара ' . number_format($this->price, 2)
                    ];
                }
            }
        }

        $characteristics = $this->category->features->whereNotIn('id',
            OzonFeaturesConstants::NOT_FOR_SHOW_ON_DETAIL)->pluck('name')->diff($this->featuresValues()->with('feature')->get()->pluck('feature.name'))->toArray();
        if ($characteristics) {
            $result[] = [
                'header' => 'Характеристики',
                'text' => 'Для оптимизации карточки товара рекомендуем заполнить характеристики:',
                'items' => $characteristics,
            ];
        }

        $specialties = $this->category->features->where('is_specialty', true)->pluck('name')->toArray();
        if ($specialties) {
            $result[] = [
                'header' => 'Особенности',
                'text' => 'Для оптимизации этой карточки товара рекомендуем использовать следующие особенности:',
                'items' => $specialties,
            ];
        }

        return $result;
    }

    /**
     * Значения характеристик
     *
     * @return HasMany
     */
    public function featuresValues(): HasMany
    {
        return $this->hasMany(OzProductFeature::class, 'product_id');
    }

    /**
     * Сброс всех триггеров
     */
    public function clearAllTriggers()
    {
        $this->clearPhotoTriggers();
        $this->clearReviewTriggers();
        $this->clearPositionTriggers();
        $this->clearFeatureTriggers();
        $this->clearRemoveFromSaleTriggers();
    }

    /**
     * Сброс триггеров фотографий
     */
    public function clearPhotoTriggers()
    {
        if ($this->minPhotosTrigger) {
            $this->photoTriggers()->delete();
        }
    }

    /**
     * Триггеры фотографий
     *
     * @return HasMany
     */
    public function photoTriggers(): HasMany
    {
        return $this->hasMany(TriggerChangePhotos::class, 'product_id');
    }

    /**
     * Сброс триггеров просмотров
     */
    public function clearReviewTriggers()
    {
        if ($this->minReviewsTrigger) {
            $this->reviewTriggers()->delete();
        }
    }

    /**
     * Триггеры обзоров
     *
     * @return HasMany
     */
    public function reviewTriggers(): HasMany
    {
        return $this->hasMany(TriggerChangeReviews::class, 'product_id');
    }

    /**
     * Сброс триггеров позиций
     */
    public function clearPositionTriggers()
    {
        $position_trigger = $this->positionHistory()->where('is_trigger', true);

        if ($position_trigger->count()) {
            $position_trigger->update(['is_trigger' => false]);
        }
    }

    /**
     * История позиции товара
     *
     * @return HasMany
     */
    public function positionHistory(): HasMany
    {
        return $this->hasMany(ProductPositionHistory::class, 'product_id');
    }

    /**
     * Аналитика для товара
     *
     * @return HasMany
     */
    public function positionAnalytics(): HasMany
    {
        return $this->hasMany(OzProductAnalyticsData::class, 'product_id');
    }

    /**
     * Escrow history
     *
     * @return HasMany
     */
    public function escrowHistory(): HasMany
    {
        return $this->hasMany(EscrowHistory::class, 'product_id', 'id');
    }

    /**
     * Сброс триггеров характеристик
     */
    public function clearFeatureTriggers()
    {
        $featureTriggers = $this->featureTriggers()->where('is_shown', false);
        if ($featureTriggers->count()) {
            $featureTriggers->update(['is_shown' => true]);
        }
    }

    /**
     * Сброс триггеров снятия с продажи
     */
    public function clearRemoveFromSaleTriggers()
    {
        $saleTriggers = $this->removeFromSaleTriggers()->where('is_shown', false);

        if ($saleTriggers->count()) {
            $saleTriggers->update(['is_shown' => true]);
        }
    }

    /**
     * Сброс флагов обновления информации о товаре.
     */
    public function resetUpdateFlags()
    {
        $this->attributes['card_updated'] = true;
        $this->attributes['characteristics_updated'] = true;
        $this->attributes['position_updated'] = true;
        $this->save();
    }

    /**
     * Поиск по полям
     * @param $query
     * @param $searchString
     * @return mixed
     */
    public function scopeSearch($query, $searchString): mixed
    {
        return $query->where(function ($query2) use ($searchString) {
            $query2->where('name', 'LIKE', '%' . $searchString . '%')
                ->orWhere('id', $searchString)
                ->orWhere('oz_products.external_id', 'LIKE', '%' . $searchString . '%')
                ->orWhere('sku_fbo', 'LIKE', '%' . $searchString . '%')
                ->orWhere('sku_fbs', 'LIKE', '%' . $searchString . '%');
        });
    }

    /**
     * Search products by brand
     * @param $query
     * @param $searchBrand
     * @return mixed
     */
    public function scopeSearchBrand($query, string $searchBrand):mixed {
        return $query->where('brand', 'LIKE', "%{$searchBrand}%");
    }

    /**
     * Выводим описания из атрибутов Ozon
     */
    public function getDescriptionsAttribute()
    {
        $feature = Feature::find(self::PRODUCT_DESCRIPTION_FEATURE_ID);

        if (!empty($feature->id)) {
            return $this->featuresValues()->where('oz_products_features.feature_id', $feature->id)->pluck('value');
        }

        return null;
    }

    /**
     * Set description or anotetion
     */
    public function updateDescriptions($value)
    {
        $feature = Feature::find(self::PRODUCT_DESCRIPTION_FEATURE_ID);
        OzProductFeature::where([
            'product_id' => $this->id,
            'feature_id' => $feature->id,
        ])->delete();
        $newDescription = OzProductFeature::create([
            'product_id' => $this->id,
            'feature_id' => $feature->id,
            'value' => $value,
        ]);
        $newDescription->save();
    }

    /**
     * Выводим коды роликов с youtube из атрибутов Ozon
     */
    public function getYoutubecodeAttribute()
    {
        $feature = Feature::find(self::PRODUCT_YOUTUBE_FEATURE_ID);
        if (!empty($feature->id)) {
            return $this->featuresValues()->where('oz_products_features.feature_id', $feature->id)->pluck('value');
        }

        return null;
    }

    /**
     * Выводим 3D изображения из атрибутов Ozon
     * Not used
     * todo delete if after 01/01/2022
     */
    public function getImages3dAttribute()
    {
        $feature = Feature::find(self::PRODUCT_3D_IMAGE_FEATURE_ID);

        if (!empty($feature->id)) {
            return $this->featuresValues()->where('oz_products_features.feature_id', $feature->id)->pluck('value');
        }

        return null;
    }

    /*
     * Получение списка позиций по товару
     */
    public function getPositions()
    {
        return ProductPositionHistory::where('product_id', $this->id)
            ->select('position', 'category', 'date')
            ->whereNotNull('position')
            ->limit(10)
            ->latest()
            ->get();
    }

    /*
     * Лучшая позиция по товару
     */
    public function getHighestPosition()
    {
        $position = ProductPositionHistory::where('product_id', $this->id)
            ->whereNotNull('position')
            ->orderBy('position', 'asc')
            ->first();
        return $position ? $position->position : null;
    }


    /**
     * Получение пользователя для реализации старых API
     */
    public function user()
    {
        if ($this->user_id == UserService::getUserId()) {
            return UserService::getUser();
        }

        return null;
    }


    /**
     * Получение аккаунта для реализации старых API
     */
    public function account()
    {
        if ($this->account_id == UserService::getAccountId()) {
            return UserService::getCurrentAccount();
        } else {
            return UserService::getAccount($this->account_id);
        }
    }

    /**
     * Список подбора для товара.
     *
     * @return HasMany
     */
    public function platformSemantic(): HasMany
    {
        return $this->hasMany(PlatfomSemantic::class, 'product_id');
    }

    /**
     * Список подбора для товара.
     *
     * @return HasMany
     */
    public function listGoodsAdd(): HasMany
    {
        return $this->hasMany(OzListGoodsAdd::class, 'oz_product_id');
    }

    /**
     * Get history positions for product
     *
     * @return HasMany
     */
    public function getOptimisationHistory(): HasMany
    {
        return $this->hasMany(OptimisationHistory::class, 'product_id', 'id');
    }

    /**
     * Escrow hash
     *
     * @return HasMany
     */
    public function escrowHash(): HasMany
    {
        return $this->hasMany(EscrowHash::class, 'product_id', 'id')->select('name', 'image_hash');
    }

    /**
     * Escrow certificate
     *
     * @return HasMany
     */
    public function certificate(): HasMany
    {
        return $this->hasMany(EscrowCertificate::class, 'product_id', 'id')->select('link', 'lang', 'full_name', 'copyright_holder', 'email', 'created_at');
    }

    /**
     * Relations for user and accounts for model from table oz_product_user for many products
     */
    public function usersAndAccounts()
    {
        return $this->hasMany(OzProductUser::class, 'external_id', 'external_id')
            ->whereNull('oz_product_user.deleted_at');
    }

    /**
     * Relations for user and accounts for model from table oz_product_user for one product
     */
    public function userAndAccount($userId, $accountId)
    {
        return $this->hasMany(OzProductUser::class, 'external_id', 'external_id')
            ->where([
                'oz_product_user.user_id' => $userId,
                'oz_product_user.account_id' => $accountId
            ])->whereNull('oz_product_user.deleted_at');
    }

    /**
     * Attach user and account to wb_products from table oz_product_user
     * @param $userId
     * @param $accountId
     * @throws \Exception
     */
    public function attachUserAndAccount($userId = 0, $accountId = 0): bool
    {
        if (!$userId || !$accountId) {
            throw new \Exception('Не заданы userId или accountId');
        }
        $attachedButNotActive = DB::table('oz_product_user')->select()->where([
            'user_id' => $userId,
            'account_id' => $accountId,
            'external_id' => $this->external_id,
        ])->whereNotNull('deleted_at');
        if ($attachedButNotActive->exists()) {
            $attachedButNotActive->update(['deleted_at' => null]);
        } else {
            DB::table('oz_product_user')->insert([
                'user_id' => $userId,
                'account_id' => $accountId,
                'external_id' => $this->external_id,
                'deleted_at' => null,
            ]);
        }
        return true;
    }


    /**
     * Detach user and account to wb_products from table oz_product_user
     * @param int $userId
     * @param int $accountId
     * @throws \Exception
     */
    public function detachUserAndAccount($userId = 0, $accountId = 0): bool
    {
        if (!$userId || !$accountId) {
            throw new \Exception('Не заданы userId или accountId');
            die();
        }
        $attachedAndActive = DB::table('oz_product_user')->select()->where([
            'user_id' => $userId,
            'account_id' => $accountId,
            'external_id' => $this->external_id
        ])->whereNull('deleted_at')->first();
        if ($attachedAndActive) {
            DB::table('oz_product_user')->where([
                'user_id' => $userId,
                'account_id' => $accountId,
                'external_id' => $this->external_id,
            ])->whereNull('deleted_at')->update(['deleted_at' => \Carbon\Carbon::now()]);
        }
        return true;
    }

    /**
     * Escrow history
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(EscrowHistory::class, 'product_id', 'id')->select([
            'id',
            'account_id',
            'product_id',
            'nmid',
            'copyright_holder',
            'full_name',
            'email',
            'created_at',
            'updated_at'
        ])->where('deleted_at', null);
    }

    /**
     * Gel list of used SKU
     *
     * @param string $skuField (sku_fbo || sku_fbs)
     * @return array
     */
    public static function getSkuUsed(string $skuField = 'sku_fbo'): array
    {
        $skuUsed = [];
        $products = self::select($skuField)
            ->currentUserAndAccount()
            ->whereNotNull($skuField)
            ->get();
        if (!$products->isEmpty()) {
            $skuUsed = $products->pluck($skuField)->all();
        }
        return $skuUsed;
    }
}
