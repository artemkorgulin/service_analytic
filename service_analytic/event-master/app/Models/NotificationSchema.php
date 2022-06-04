<?php

namespace App\Models;

use AnalyticPlatform\LaravelHelpers\Constants\Notifications\WayCodes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationSchema extends Model
{

    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'type_id',
        'way_code',
        'user_ip',
        'delete_user_ip'
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


    /*** Scopes ***/

    /**
     * Scope query to only include schemas
     * of specified user
     *
     * @param  numeric|User  $user
     *
     * @return void
     */
    public function scopeOfUser(Builder $query, int|string|User $user): void
    {
        if (is_numeric($user)) {
            $query->where('user_id', $user);
        } elseif (is_object($user)) {
            $query->where('user_id', $user->id);
        }
    }


    /**
     * Scope query to only include schemas
     * with specified way code
     *
     * @param  Builder  $query
     * @param  string  $wayCode
     *
     * @return void
     */
    public function scopeOfWayCode(Builder $query, string $wayCode): void
    {
        $query->where('way_code', $wayCode);
    }


    /**
     * Scope query to only include schemas
     * with telegram way code
     *
     * @param  Builder  $query
     *
     * @return void
     */
    public function scopeOfTelegram(Builder $query): void
    {
        $query->where('way_code', WayCodes::TELEGRAM);
    }


    /**
     * Scope query to only include schemas
     * of specified type
     *
     * @param  Builder  $query
     * @param  string|int|NotificationType  $type
     *
     * @return void
     */
    public function scopeOfType(Builder $query, string|int|NotificationType $type)
    {
        if (is_numeric($type)) {
            $query->where('type_id', $type);
        } elseif (is_object($type)) {
            $query->where('type_id', $type->id);
        }
    }
}
