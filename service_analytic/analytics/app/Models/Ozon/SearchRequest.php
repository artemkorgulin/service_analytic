<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;

class SearchRequest extends Model
{
    use HasFactory;

    protected $table = 'search_requests';

    protected $connection = 'ozon';

    /**
     * Scope a query to only include popular users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActive(\Illuminate\Database\Eloquent\Builder $query): Builder
    {
        return $query->where('is_active', '=', '1');
    }
}
