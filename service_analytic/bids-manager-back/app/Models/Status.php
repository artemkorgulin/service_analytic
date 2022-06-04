<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Status
 *
 * @package App\Models
 *
 * @property string $code
 * @property string $name
 */
class Status extends Model
{
    const ACTIVE     = 1;
    const NOT_ACTIVE = 2;
    const ARCHIVED   = 3;
    const DELETED    = 4;
    const DRAFT      = 5;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    public  $table = 'statuses';


    /**
     * Товары в РК
     *
     * @return HasMany
     */
    public function campaignProducts()
    {
        return $this->hasMany(CampaignProduct::class);
    }
}
