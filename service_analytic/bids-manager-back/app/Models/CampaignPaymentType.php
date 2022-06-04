<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CampaignPaymentType
 *
 * @package App\Models
 */
class CampaignPaymentType extends Model
{
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
