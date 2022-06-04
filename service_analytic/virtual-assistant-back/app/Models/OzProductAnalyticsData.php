<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OzProductAnalyticsData extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'account_id', 'product_id', 'external_id', 'sku', 'name', 'report_date', 'hits_view_search',
        'hits_view_pdp', 'hits_view', 'hits_tocart_search', 'hits_tocart_pdp', 'hits_tocart', 'session_view_search',
        'session_view_pdp', 'session_view', 'conv_tocart_search', 'conv_tocart_pdp', 'conv_tocart', 'revenue',
        'returns', 'cancellations', 'ordered_units', 'delivered_units', 'adv_view_pdp', 'adv_view_search_category',
        'adv_view_all', 'adv_sum_all', 'position_category', 'postings', 'postings_premium', 'created_at', 'updated_at',
    ];
}
