<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUser extends Model
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
        'user_id',
        'notification_id',
        'is_read',
    ];

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
