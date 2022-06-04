<?php

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Model;

class OzHistoryProduct extends Model
{
    protected $connection = 'analytica_oz';
    protected $table = 'oz_history_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'account_id',
        'vendor_code',
        'category_id',
        'subject_id',
        'position',
        'position_category',
        'position_search',
        'date',
        'rating',
        'optimization',
        'comments',
        'images',
        'escrow',
        'current_sales',
        'id_history_top36',
        'url',
    ];
}
