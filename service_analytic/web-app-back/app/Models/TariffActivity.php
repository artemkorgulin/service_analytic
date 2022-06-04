<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TariffActivity extends Model
{
    use HasFactory, LogsActivity;

    const WAIT = 0; // Ожидает
    const ACTVE = 1; // Активный
    const INACTVE = 2; // Неактивный

    protected $table = 'tariff_activity';

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date'
    ];

    protected $fillable = ['id', 'name', 'description', 'price_id', 'period', 'payment_subject', 'payment_mode'];

    /**
     * Properties logging model
     */
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'tariff_activity';

    /**
     * Get Order
     *
     * @return BelongsToMany
     */
    public function order(): BelongsToMany
    {
        return $this->belongsToMany('order_id', Order::class);
    }


    public function tariff(): BelongsTo
    {
        return $this->belongsTo(OldTariff::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
