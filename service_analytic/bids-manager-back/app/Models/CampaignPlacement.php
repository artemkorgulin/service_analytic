<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Class CampaignPlacement
 *
 * @package App\Models
 */
class CampaignPlacement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'description'
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
