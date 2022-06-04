<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Keyword
 *
 * @package App\Models
 *
 * @property string $name
 * @property integer $category_id
 * @property mixed $id
 *
 * @method static Keyword find(int $id)
 */
class Keyword extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Ключевые слова в рекламных кампаниях
     *
     * @return HasMany
     */
    public function campaignKeywords(): HasMany
    {
        return $this->hasMany(CampaignKeyword::class);
    }

    /**
     * Товары в РК
     *
     * @return BelongsToMany
     */
    public function campaignProducts(): BelongsToMany
    {
        return $this->belongsToMany(CampaignProduct::class);
    }

    /**
     * Группы
     *
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public static function saveKeyword(array $request): Builder|Model|bool
    {
        if (!array_key_exists('keyword_name', $request)) {
            return false;
        }

        $keyword = self::query()->where('name', '=', $request['keyword_name'])->first();

        if (!empty($keyword)) {
            return $keyword;
        }

        $keyword = new Keyword();
        $keyword->name = $request['keyword_name'];

        if (!empty($request['campaign_product_id'])) {
            $keyword->category_id = CampaignProduct::getTopCategoryById($request['campaign_product_id']);
        }

        $keyword->save();

        return $keyword;
    }
}
