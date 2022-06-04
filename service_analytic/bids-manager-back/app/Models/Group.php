<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Group
 *
 * @package App\Models
 *
 * @property string $name
 * @property int    $campaign_id
 * @property int    $ozon_id
 * @property int    $status_id
 */
class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'campaign_id',
        'ozon_id',
        'status_id',
    ];

    /**
     * Рекламная кампания
     *
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Ключевые слова
     *
     * @return BelongsToMany
     */
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'campaign_keywords');
    }

    /**
     * Минус-слова
     *
     * @return BelongsToMany
     */
    public function stopWords(): BelongsToMany
    {
        return $this->belongsToMany(StopWord::class, 'campaign_stop_words')
                    ->using(CampaignStopWord::class);
    }

    /**
     * Статус
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Связка РК, товары, группа
     *
     * @return BelongsToMany
     */
    public function campaignProducts(): BelongsToMany
    {
        return $this->belongsToMany(CampaignProduct::class, 'campaign_products');
    }

    /**
     * Только не удалённые группы
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('status_id', '<>', Status::DELETED);
    }

    /**
     * Сохранить и обновить группу
     *
     * @param  array  $data
     * @param  Campaign  $campaign
     * @return $this
     */
    public function saveGroup(array $data, Campaign $campaign): Group
    {
        $this->fill($data);
        $this->campaign()->associate($campaign);
        $this->status_id = Status::ACTIVE;
        $this->save();

        /**
         * TODO изменить структуру данных. Должно быть две таблицы связки. Товары и рекламными компаниями и группы с товарами.
         * После чего с помощью метода sync будет автоматически обновляться связь группы с товарами.
         * Код, ниже необходимо будет удалить.
         */
        if (array_key_exists('products', $data)) {
            CampaignProduct::where([['group_id', '=', $this->id], ['campaign_id', '=', $campaign->id]])->update([
                'group_id' => null,
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
        }

        if (!empty($data['products'])) {
            CampaignProduct::where('campaign_id', '=', $campaign->id)->whereIn('product_id', $data['products'])->update([
                'group_id' => $this->id,
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
        }

        return $this;
    }
}
