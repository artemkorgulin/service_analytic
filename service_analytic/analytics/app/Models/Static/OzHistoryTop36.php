<?php

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Model;

class OzHistoryTop36 extends Model
{
    protected $connection = 'analytica_oz';
    protected $table = 'oz_history_top36';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendor_code',
        'category_id',
        'subject_id',
        'date',
        'rating_avg',
        'comments_avg',
        'position',
        'position_category',
        'position_search',
        'images_avg',
        'sale_avg',
    ];
}
