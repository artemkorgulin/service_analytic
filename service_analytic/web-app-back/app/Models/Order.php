<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Платёж
 * @property int id
 * @property int order_id
 * @property json tariff_ids
 * @property json tariff_activity_ids
 * @property int user_id
 * @property enum type  enum('bank_card', 'bank') not null comment 'Тип платежа карта или счет',
 * @property int recipient_id
 * @property int abolition_reason_id
 * @property int payment_method_id
 * @property int subscription_id
 * @property double amount приходит из кассы
 * @property bool income_amount сумма платежа, приходит из кассы
 * @property string status статус из кассы
 * @property string currency валюта, приходит из кассы
 * @property string yookassa_id id платежа в кассе
 * @property string description
 * @property bool test тестовый ли платеж, приходит из кассы
 * @property bool paid признак оплаты, приходит из кассы
 * @property bool refundable признак возврата, приходит из кассы, сейчас не используется
 * @property array receipt данные чека, отправляются в кассу, содержат услугу
 * @property array transfers данные о распределении между магазинами, приходят от кассы, сейчас не используются
 * @property string receipt_registration статус доставки чека, приходит из кассы
 * @property string idempotence_key ключ идемпотентности для запроса об оплате. Используется также для получения статуса об оплаты
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property DateTime deleted_at
 * @property DateTime captured_at
 */
class Order extends Model
{
    use HasFactory, LogsActivity;

    const  SUCCEEDED = 'succeeded';
    const  CANCELED = 'canceled';
    const  PENDING = 'pending';

    const CURRENCY_RUB = 'rub';

    const TYPE_BANK = 'bank';
    const TYPE_BANK_CARD = 'bank_card';

    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'tariff_ids',
        'amount',
        'status',
        'currency',
        'period',
        'user_id'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'transfers' => 'array',
        'receipt' => 'array',
        'captured_at' => 'datetime',
    ];

    protected $dates = ['captured_at', 'start_at', 'end_at'];

    /**
     * Properties logging model
     */
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'orders';


    /**
     * @return BelongsTo
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(PaymentRecipient::class, 'recipient_id');
    }

    /**
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return BelongsTo
     */
    public function oldTariff(): BelongsTo
    {
        return $this->belongsTo(OldTariff::class);
    }

    /**
     * @return BelongsTo
     */
    public function tariffActivity(): BelongsTo
    {
        return $this->belongsTo(TariffActivity::class);
    }

    /**
     * @return BelongsTo
     */
    public function abolitionReason(): BelongsTo
    {
        return $this->belongsTo(AbolitionReason::class);
    }

    /**
     * Сохранить order
     *
     * @param  array  $data
     *
     * @return $this
     */
    public function saveOrder(array $data): self
    {
        $this->fill($data);
        $this->save();

        return $this;
    }

    /**
     * Получить связанную запись по промокоду для этого заказа, если она есть
     */
    public function promocodeUser()
    {
        return $this->hasOne(PromocodeUser::class);
    }

    /**
     * Scope a query to only include succeeded payments
     *
     * @param  Builder  $query
     *
     * @return void
     */
    public function scopeSucceeded(Builder $query): void
    {
        $query->where('status', self::SUCCEEDED);
    }

    /**
     * Получить тариф, по которому сформирован заказ
     */
    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    /**
     * Получить компанию, для которой куплен корпоративный доступ
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include order of given type
     *
     * @param $query
     * @param  string|null  $type
     *
     * @return void
     */
    public function scopeOfType(Builder $query, ?string $type = null)
    {
        if ($type) {
            $query->where('type', $type);
        }
    }

    /**
     * Активные заказы
     */
    public function scopeActive(Builder $query)
    {
        return $query
            ->where('status', self::SUCCEEDED)
            ->where('start_at', '<=', DB::raw('now()'))
            ->where('end_at', '>=', DB::raw('now()'));
    }

    /**
     * Получить услуги, ассоциированные с этим заказом
     */
    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot('ordered_amount', 'total_price');
    }

    /**
     * Получить пользователя для данного заказа
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return int
     */
    public function getEscrow(): int
    {
        return $this->period * 100;
    }
}
