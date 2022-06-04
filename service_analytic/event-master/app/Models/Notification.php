<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

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
        'template_id',
        'message',
        'type_id',
        'subtype_id',
        'created_at',
        'deleted_at',
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

    /**
     * Получить отношение к подтипу.
     *
     * @return BelongsTo
     */
    public function subtype()
    {
        return $this->belongsTo(NotificationSubtype::class, 'subtype_id');
    }

    /**
     * Получить отношение к шаблону.
     *
     * @return BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Получить отношение к пользователям.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(NotificationUser::class, 'notification_id');
    }
}
