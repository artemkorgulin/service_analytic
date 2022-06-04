<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Characteristic
 *
 * @package App\Models
 *
 * @property string  $id
 * @property string  $name
 * @property integer $rating
 * @property integer $search_query_id
 */
class Characteristic extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'rating'];

    /**
     * Связь КЗ + ПЗ
     *
     * @return BelongsToMany
     */
    public function rootQueriesSearchQueries()
    {
        return $this->belongsToMany(
            RootQuerySearchQuery::class,
            'characteristic_root_query_search_query',
            'characteristic_id',
            'root_query_search_query_id'
        );
    }
}
