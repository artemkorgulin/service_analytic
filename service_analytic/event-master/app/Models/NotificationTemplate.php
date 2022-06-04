<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'template',
        'lang',
        'subtype_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Получить отношение к подтипу.
     *
     * @return BelongsTo
     */
    public function subtype()
    {
        return $this->belongsTo(NotificationSubtype::class, 'subtype_id');
    }

}
