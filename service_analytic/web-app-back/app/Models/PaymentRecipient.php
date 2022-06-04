<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Получатель платежа
 * @property int id
 * @property int account_id получатель платежа, приходит из кассы
 * @property int gateway_id субаккаунт, приходит из кассы
 * @property DateTime created_at
 * @property DateTime updated_at
 */
class PaymentRecipient extends Model
{
    use HasFactory;
}
