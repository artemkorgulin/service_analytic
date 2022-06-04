<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Тариф
 * заполняется из сидов
 * @property int id
 * @property string description
 * @property int price_id
 * @property int period
 * @property string payment_subject
 * @property string payment_mode
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property DateTime deleted_at
 */
class OldTariff extends Model
{
    use HasFactory;

    const TARIFF_FREE_ID  = 2;
    const TARIFF_PROMO_ID = 1;

    protected $fillable = ['id', 'name', 'description', 'price_id', 'period', 'payment_subject', 'payment_mode'];

    /**
     * Диапазон запроса, включающий только активные и видимые тарифы.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeVisible($query): Builder
    {
        return $query->where('visible', '=', 1);
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'tariff_permissions', 'tariff_id', 'permission_id');
    }

    public function price()
    {
        //return $this->belongsTo(TariffPrice::class, 'price_id');
        return $this->belongsTo(TariffPrice::class, 'tariff_id');
    }

    public function tariffPrice()
    {
        return $this->hasOne(TariffPrice::class, 'tariff_id', 'tariff_id');
    }

    /**
     * Получить бесплатный тариф
     *
     * @return mixed
     */
    public static function getFree()
    {
        return OldTariff::where('tariff_id', '=', OldTariff::TARIFF_FREE_ID)->firstOrFail();
    }

    /**
     * Получить промо тариф
     *
     * @return mixed
     */
    public static function getPromo()
    {
        return OldTariff::where('tariff_id', '=', OldTariff::TARIFF_PROMO_ID)->firstOrFail();
    }

    /**
     * Получить тарифы пользователя
     *
     * @return array
     */
    public static function getTariffsActivity($tariffActivity): array
    {
        $result = [];
        if ($tariffActivity === null) {
            $result['tariff'] = OldTariff::query()->select()->where('tariff_id', '=',
                OldTariff::TARIFF_FREE_ID)->get()->toArray();

            $result['tariff'][0]['escrow'] = 3;
        } else {
            $order = Order::select('id', 'amount', 'period', 'type', 'captured_at')
                ->where('id', '=', $tariffActivity->order_id)->first();

            $order->start_at = (new Carbon($order->captured_at))->format('d.m.Y');
            $order->end_at   = (new Carbon($tariffActivity->end_at))->format('d.m.Y');
            $result['order'] = $order->toArray();

            $result['tariff'] = OldTariff::select('tariff_id', 'name', 'description', 'sku')
                ->where('tariff_id', '=', $tariffActivity->tariff_id)->get()->toArray();

            $result['tariff'][0]['escrow'] = $order->getEscrow();
        }

        return $result;
    }


    /*** Scopes ***/

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tariff_activity')
            ->as('activity')
            ->withPivot('status', 'start_at', 'end_at');
    }
}
