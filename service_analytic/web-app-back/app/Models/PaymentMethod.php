<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Способ оплаты
 * @property int id
 * @property string type приходит из кассы
 * @property int yookassa_id id способа оплаты кассы приходит из кассы
 * @property bool saved параметр автоплатежа приходит из кассы
 * @property string title название способа оплаты приходит из кассы
 * @property DateTime created_at
 * @property DateTime updated_at
 */
class PaymentMethod extends Model
{
    use HasFactory;
}
