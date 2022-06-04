<?php

namespace App\Models;

use App\Enums\OzonPerformance\Campaign\CampaignType as CampaignTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CampaignType
 *
 * @package App\Models
 * @method static Builder type(CampaignTypeEnum $type)
 */
class CampaignType extends Model
{

    const SKU          = 1; // Реклама товаров в спонсорских полках. Размещается на карточке товаров, в поиске и категории
    const BANNER       = 2; // Баннерная рекламная акция
    const SIS          = 3; // Реклама магазина
    const BRAND_SHELF  = 4; // Брендовая полка
    const BOOSTING_SKU = 5; // Повышение товаров в каталоге
    const ACTION       = 6; // Рекламная кампания для селлерских акций
    const ACTION_CAS   = 7; // Рекламная кампания для акции

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    /*** Scopes ***/

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Enums\OzonPerformance\Campaign\CampaignType|string  $type
     *
     * @return void
     */
    public function scopeType(Builder $query, CampaignTypeEnum|string $type)
    {
        if ($type instanceof CampaignTypeEnum) {
            $type = $type->getKey();
        }
        $query->where('code', $type);
    }
}
