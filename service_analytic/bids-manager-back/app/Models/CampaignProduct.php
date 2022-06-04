<?php

namespace App\Models;

use App\Repositories\V2\Product\CategoryRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CampaignProduct
 *
 * @package App\Models
 *
 * @property int $campaign_id
 * @property int $product_id
 * @property int $group_id
 * @property int $status_id
 *
 * @property-read CampaignKeyword[] $campaignKeywords
 * @property-read Group $group
 * @property mixed $campaign
 *
 * @method static CampaignProduct find(int $campaignProductId)
 */
class CampaignProduct extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'campaign_id',
        'product_id',
        'group_id',
        'status_id',
    ];

    /**
     * Рекламная кампания
     *
     * @return BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Группа
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Статус
     *
     * @return BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Ключевики в рекламных кампаниях
     *
     * @return HasMany
     */
    public function campaignKeywords()
    {
        return $this->hasMany(CampaignKeyword::class);
    }

    /**
     * Минус-слова в рекламных кампаниях
     *
     * @return HasMany
     */
    public function campaignStopWords()
    {
        return $this->hasMany(CampaignStopWord::class);
    }

    /**
     * Ключевые слова
     *
     * @return BelongsToMany
     */
    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'campaign_keywords');
    }

    /**
     * Минус-слова
     *
     * @return BelongsToMany
     */
    public function stopWords()
    {
        return $this->belongsToMany(StopWord::class, 'campaign_stop_words')
            ->using(CampaignStopWord::class);
    }

    /**
     * Статистика
     *
     * @return HasMany
     */
    public function statistics()
    {
        return $this->hasMany(CampaignProductStatistic::class);
    }

    public static function getTopCategoryById(int $id)
    {
        $category = CategoryRepository::getProductCategory($id);

        if (!empty($category)) {
            $topCategory = $category ? CategoryRepository::getTopCategory($category) : null;

            return $topCategory ? $topCategory->id : null;
        }

        return null;
    }
}
