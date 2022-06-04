<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OzAlias
 *
 * @package App\Models
 *
 * @property int    $id
 * @property string $name
 * @property int    $root_query_id
 */
class OzAlias extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Корневой запрос
     *
     * @return BelongsTo
     */
    public function rootQuery()
    {
        return $this->belongsTo(RootQuery::class);
    }
}
