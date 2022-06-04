<?php

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbHistoryProduct extends Model
{
    protected $connection = 'analytica';
    protected $table = 'wb_history_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable
        = [
            'user_id',
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
