<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoselectParameter extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'group_id',
        'campaign_product_id',
        'keyword',
        'category_id',
        'date_from',
        'date_to'
    ];

    /**
     * @return HasOne
     */
    public function group()
    {
        return $this->hasOne(Group::class);
    }

    /**
     * @return HasOne
     */
    public function campaignProduct()
    {
        return $this->hasOne(CampaignProduct::class);
    }

    /**
     * @return HasMany
     */
    public function results()
    {
        return $this->hasMany(AutoselectResult::class);
    }

    public function delete()
    {
        $this->results()->delete();
        return parent::delete();
    }
}
