<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CampaignPageType
 *
 * @package App\Models
 */
class CampaignPageType extends Model
{
    const SEARCH  = 1;  // Поиск
    const PRODUCT = 2;  // Карточка товара и категории

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Рекламная кампания
     *
     * @return HasMany
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
