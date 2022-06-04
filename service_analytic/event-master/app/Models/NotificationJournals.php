<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationJournals extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'notification_id',
        'message',
        'is_read',
        'date',
        'subtype_id',
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

    /**
     * Получить отношение к уведомлению.
     *
     * @return BelongsTo
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
