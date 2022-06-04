<?php

namespace App\Models;

use App\Constants\OzonFeaturesConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Class OzCategory
 * Категория товара
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property integer $external_id id категории в Озон
 * @property integer|null $parent_id
 * @property integer $count_features кол-во характеристик
 * @property integer $is_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feature[] $features
 * @property-read int|null $features_count
 * @property-read string $bread_crumbs
 * @property-read array $characteristics
 * @property-read OzCategory|null $parentCategory
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereCountFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OzCategory extends Model
{

    protected $table = 'oz_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'external_id', 'parent_id', 'count_features', 'is_deleted', 'settings'
    ];

    protected $hidden = [
        'count_features', 'is_deleted', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
    ];


    /**
     * Возвращает external_id
     * @param $id
     * @return mixed
     */
    public static function getExternalId($id)
    {
        return optional(self::find($id))->external_id;
    }

    /**
     * Все родительские категории (рекурсия)
     *
     * @return BelongsTo
     */
    public function parents(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->with(['parents']);
    }

    /**
     * Родительская категория.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Все родительские категории (рекурсия).
     *
     * @return BelongsTo
     */
    public function recursiveParents(): BelongsTo
    {
        return $this->parent()->with('recursiveParents');
    }

    /**
     * Получить всех детей
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Все дети прям все при все (рекурсия почему-то не работает)
     *
     * @return  HasMany
     */
    public function recursiveChildren(): HasMany
    {
        return $this->children()->with('recursiveChildren');
    }


    /**
     * Все дети прям все привсе (рекурсия)
     *
     * @return BelongsTo
     */
    public static function allChildrenIds($id, &$storedArray = [])
    {
        $category = self::findOrFail($id);
        if (!in_array($id, $storedArray)) {
            $storedArray[] = (int)$id;
        }
        foreach ($category->children()->get() as $subCategory) {
            self::allChildrenIds($subCategory->id, $storedArray);
        }
        return $storedArray;
    }

    /**
     * Все дети уровня $level($level = 3 от главной категории - это subject).
     *
     * @param $level
     * @param $storedArray
     * @param $currentLevel
     * @return array
     */
    public function allSubjects($level, &$storedArray = [], $currentLevel = 1): array
    {
        if ($level == $currentLevel) {
            $storedArray[$this->id] = $this->name;
        } else {
            foreach ($this->children as $subCategory) {
                $subCategory->allSubjects($level, $storedArray, $currentLevel + 1);
            }
        }

        return $storedArray;
    }


    /**
     * Только главные (корневые директории Озон)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Все дети (потомки)
     */
    public function scopeChildren($query, $parent_id)
    {
        return $query->where('parent_id', $parent_id);
    }

    /**
     * Родительская категория
     *
     * @return BelongsTo
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Характеристики
     *
     * @return BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'oz_category_to_feature', 'category_id', 'feature_id');
    }

    /**
     * Характеристики в формате для вывода
     *
     * @return array
     */

    public function getCharacteristicsAttribute(): array
    {
        $result = [];

        foreach ($this->features as $feature) {
            if (!in_array($feature->id, OzonFeaturesConstants::NOT_FOR_SHOW_ON_DETAIL)) {
                $item = [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'is_required' => $feature->is_required,
                    'is_collection' => $feature->is_collection,
                    'is_reference' => $feature->is_reference,
                    'is_specialty' => $feature->is_specialty,
                    'description' => $feature->description,
                    'type' => $feature->type,
                ];
                if ($feature->is_collection) {
                    $item['chosen_count'] = 0;
                    $item['count'] = $feature->count_values;
                }
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Получить хлебные крошки
     * @return string
     */
    public function getBreadCrumbsAttribute(): string
    {
        return $this->parentCategory ? $this->parentCategory->breadCrumbs . ' - ' . $this->name : $this->name;
    }

    /**
     * Поиск по категориям Ozon
     * @return string
     */
    public function scopeSearch($query, $searchString = '')
    {
        if (trim($searchString)) {
            return $query->where(function ($query2) use ($searchString) {
                $query2->where('name', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('id', $searchString)
                    ->orWhere('external_id', 'LIKE', '%' . $searchString . '%');
            });
        }

        return $query;
    }
}
