<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Платёж
 * @property int id
 * @property int recipient_id
 * @property int abolition_reason_id
 * @property int payment_method_id
 * @property int tariff_id
 * @property int subscription_id
 * @property double amount приходит из кассы
 * @property string status статус из кассы
 * @property bool income_amount сумма платежа, приходит из кассы
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
class Payment extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $casts = [
        'transfers' => 'array',
        'receipt' => 'array',
        'captured_at' => 'datetime'
    ];

    protected $dates = ['captured_at'];

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
    public function tariff(): BelongsTo
    {
        return $this->belongsTo(OldTariff::class);
    }

    /**
     * @return BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo
     */
    public function abolitionReason(): BelongsTo
    {
        return $this->belongsTo(AbolitionReason::class);
    }
}
