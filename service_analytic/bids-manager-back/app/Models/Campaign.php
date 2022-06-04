<?php

namespace App\Models;

use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Framework\AppModel;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Class Campaign
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $ozon_id
 * @property string $name
 * @property integer $account_id
 * @property integer $campaign_status_id
 * @property integer $type_id
 * @property integer $page_type_id
 * @property double $budget
 * @property int $placement_id
 * @property int $payment_type_id
 * @property string $start_date
 * @property string $end_date
 * @property string $last_ozon_sync
 * @property string $last_vp_sync
 * @property string $ozon_data_date
 * @property string $vp_data_date
 *
 * @property-read CampaignStatus $campaignStatus
 * @property-read campaignType $campaignType
 * @property-read CampaignPageType $campaignPageType
 * @property-read CampaignProduct[] $campaignProducts
 *
 * @method static Campaign find(int $campaignId)
 */
class Campaign extends AppModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'state',
        'budget',
        'type_id',
        'placement_id',
        'payment_type_id',
        'page_type_id',
        'start_date',
        'end_date'
    ];

    /**
     * Статус
     *
     * @return BelongsTo
     */
    public function campaignStatus()
    {
        return $this->belongsTo(CampaignStatus::class);
    }

    /**
     * Товары
     *
     * @return HasMany
     */
    public function campaignProducts()
    {
        return $this->hasMany(CampaignProduct::class);
    }

    /**
     * Группы
     *
     * @return HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Статистика
     *
     * @return HasMany
     */
    public function statistics()
    {
        return $this->hasMany(CampaignStatistic::class);
    }

    public static function addStatisticsSumColumns($query, $groupBy = 'campaign_id')
    {
        return $query->select([
            $groupBy,
            DB::raw('SUM(popularity) AS popularity'),
            DB::raw('SUM(shows) AS shows'),
            DB::raw('ROUND((100 * shows / popularity)) as purchased_shows'),
            DB::raw('SUM(clicks) AS clicks'),
            DB::raw('ROUND((100 * clicks / shows), 2) as ctr'),
            DB::raw('ROUND((1000 * cost / shows), 2) as avg_1000_shows_price'),
            DB::raw('ROUND((cost / clicks), 2) as avg_click_price'),
            DB::raw('ROUND(SUM(cost), 2) AS cost'),
            DB::raw('SUM(orders) AS orders'),
            DB::raw('ROUND(SUM(profit), 2) AS profit'),
            DB::raw('ROUND(((cost / profit) * 100), 2) as  drr'),
            DB::raw('ROUND((cost / orders), 2) as cpo'),
            DB::raw('ROUND((cost / profit), 2) as acos'),
        ])->groupBy($groupBy);
    }

    /**
     * Суммарная статистика
     *
     * @return HasOne
     */
    public function sumStatistics()
    {
        return self::addStatisticsSumColumns($this->hasOne(CampaignStatistic::class)->withDefault(function () {
            return new Collection([
                'popularity' => null,
                'shows' => null,
                'clicks' => null,
                'cost' => null,
                'orders' => null,
                'profit' => null,
                'drr' => null,
                'cpo' => null,
                'acos' => null,
                'purchased_shows' => null,
                'ctr' => null,
                'avg_1000_shows_price' => null,
                'avg_click_price' => null
            ]);
        }));
    }

    /**
     * Стратегия
     *
     * @return BelongsTo
     */
    public function strategy()
    {
        return $this->belongsTo(Strategy::class, 'id', 'campaign_id');
    }

    /**
     * Получить счётчики по стратегии показы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function strategyShowCounts()
    {
        return $this->hasOneThrough(
            StrategyShows::class, // Класс конечной модели для получения счётчиков по стратегии
            Strategy::class, // Класс стратегии через который нужно получить счётчики
            'campaign_id', // Внешний ключ в таблице strategies
            'strategy_id', // Внешний ключ в таблице strategies_shows или таблице strategies_cpo
            'id', // Локальный ключ в таблице campaigns
            'id' // Локальный ключ в таблице strategies
        );
    }

    /**
     * Получить счётчики по стратегии CPO
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function strategyCpoCounts()
    {
        return $this->hasOneThrough(
            StrategyCpo::class,
            Strategy::class,
            'campaign_id',
            'strategy_id',
            'id',
            'id'
        );
    }

    /**
     * Тип оплаты
     *
     * @return BelongsTo
     */
    public function paymentType()
    {
        return $this->belongsTo(CampaignPaymentType::class, 'payment_type_id', 'id');
    }

    /**
     * Место размещения
     *
     * @return BelongsTo
     */
    public function placement()
    {
        return $this->belongsTo(CampaignPlacement::class, 'placement_id', 'id');
    }

    /**
     * Тип
     *
     * @return BelongsTo
     */
    public function campaignType()
    {
        return $this->belongsTo(CampaignType::class);
    }

    /**
     * Тип страницы
     *
     * @return BelongsTo
     */
    public function campaignPageType()
    {
        return $this->belongsTo(CampaignPageType::class, 'page_type_id', 'id');
    }

    /**
     * Проверка на активность компании
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        $activeStatus = CampaignStatus::where('code', CampaignStatus::ACTIVE)->first();
        return $this->campaign_status_id === $activeStatus->id;
    }

    public function saveCampaign(array $data)
    {
        ModelHelper::transaction(function () use ($data) {
            $this->fill($data);
            $this->budget = $this->budget ?? 0;
            $status = CampaignStatus::where('code', '=', CampaignStatus::DRAFT)->first();
            $this->campaign_status_id = optional($status)->id;
            $this->user_id = UserService::getUserId();
            $this->account_id = UserService::getCurrentAccountId();
            $campaignType = CampaignType::find(CampaignType::SKU);
            $this->type = $campaignType->code;
            $this->type_id = $campaignType->id;
            $this->page_type_id = $data->page_type_id ?? CampaignPageType::SEARCH;
            $this->save();

            if (!empty($data['strategy_type_id'])) {
                Strategy::saveStrategy($data, $this);
            }
        });

        return $this->load([
            'paymentType',
            'strategy',
            'campaignStatus',
            'placement',
            'campaignType',
            'sumStatistics',
            'strategyShowCounts',
            'strategyCpoCounts'
        ]);
    }
}
