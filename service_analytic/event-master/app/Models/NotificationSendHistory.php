<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSendHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_send_history';

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
        'notification_id',
        'user_id',
        'way_code',
        'created_at',
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
