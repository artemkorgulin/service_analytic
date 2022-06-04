<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoselectResult extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'autoselect_parameter_id',
        'va_request_id',
        'name',
        'date',
        'popularity',
        'cart_add_count',
        'avg_cost',
        'crtc',
        'category_id',
        'category_popularity',
        'category_cart_add_count',
        'category_avg_cost',
        'category_crtc',
    ];

    /**
     * @return BelongsTo
     */
    public function parameters()
    {
        return $this->belongsTo(AutoselectParameter::class);
    }

    /**
     * @param $value
     */
    public function setCrtcAttribute($value)
    {
        $this->attributes['crtc'] = round($value, 3);
    }

    /**
     * @param $value
     */
    public function setCategoryCrtcAttribute($value)
    {
        $this->attributes['category_crtc'] = round($value, 3);
    }
}
