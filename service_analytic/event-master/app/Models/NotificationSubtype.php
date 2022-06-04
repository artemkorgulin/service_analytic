<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSubtype extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'type_id',
        'code',
    ];

    /**
     * Получить отношение к типу.
     *
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(NotificationType::class, 'type_id');
    }
}
