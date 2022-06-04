<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Цена тарифа
 * заполняется в сидах
 * @property int id
 * @property double price
 * @property string currency
 * @property int vat_code из документации кассы
 * @property DateTime created_at
 * @property DateTime updated_at
 */
class TariffPrice extends Model
{
    use HasFactory;

    protected $table = 'tariff_prices';

    /**
     * @return hasOne
     */
    public function tariff(): hasOne
    {
        return $this->hasOne(OldTariff::class, 'tariff_id', 'tariff_id');
    }
}
