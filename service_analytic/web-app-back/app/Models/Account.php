<?php

namespace App\Models;

use App\Http\Requests\Api\SettingsRequest;
use App\Services\AccessService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class Account
 * Аккаунты для платформ
 *
 * @package App\Models
 *
 * @property $id
 */
class Account extends Model
{
    use HasFactory, HasPermissions, HasRoles, SoftDeletes, Actionable;

    const SELLER_OZON_PLATFORM_ID = 1;
    const ADM_OZON_PLATFORM_ID = 2;
    const SELLER_WILDBERRIES_PLATFORM_ID = 3;
    const SELLER_ALIEXPRESS_PLATFORM_ID = 4;
    const SELLER_AMAZON_PLATFORM_ID = 5;
    const COUNT_ADM_REQUEST_FOR_SERVERS = [
        'http://127.0.0.1:81' => 300,
        'https://back.dev.sellerexpert.ru' => 500,
        'https://back.test.sellerexpert.ru' => 500,
        'https://back.lk.sellerexpert.ru' => 18000
    ];

    protected $fillable = [
        'id',
        'platform_id',
        'platform_client_id',
        'platform_api_key',
        'title',
        'description',
        'is_selected',
        'params',
        'max_count_request_per_day',
        'current_count_request'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = ['params' => 'json'];

    protected $appends = ['platform_title'];

    protected $guard_name = 'admin';

    /**
     * Get all active adm accounts
     * @return mixed
     */
    public static function getAllAdmAccounts($platformId = self::ADM_OZON_PLATFORM_ID)
    {
        return Account::query()
            ->where('platform_id', $platformId)
            ->get();
    }

    /**
     * Get all active ozon seller accounts
     * @return mixed
     */
    public static function getAllSellerOzonAccounts($platformId = self::SELLER_OZON_PLATFORM_ID)
    {
        return Account::query()
            ->where('platform_id', $platformId)
            ->get();
    }

    /**
     * Get all active wildberries seller accounts
     * @return mixed
     */
    public static function getAllSellerWildberriesAccounts($platformId = self::SELLER_WILDBERRIES_PLATFORM_ID)
    {
        return Account::query()
            ->where('platform_id', $platformId)
            ->get();
    }

    /**
     * Get all active aliexpress seller accounts
     * @return mixed
     */
    public static function getAllSellerAliExpressAccounts($platformId = self::SELLER_ALIEXPRESS_PLATFORM_ID)
    {
        return Account::query()
            ->where('platform_id', $platformId)
            ->get();
    }

    /**
     * Get all active amazon seller accounts
     * @return mixed
     */
    public static function getAllSellerAmazonAccounts($platformId = self::SELLER_AMAZON_PLATFORM_ID)
    {
        return Account::query()
            ->where('platform_id', $platformId)
            ->get();
    }

    /**
     * Get max count request for current server
     *
     * @param int $platformId
     * @return int
     */
    public static function getMaxCountRequestPerDayForPlatform(int $platformId): int
    {
        if ($platformId === self::ADM_OZON_PLATFORM_ID) {
            return self::COUNT_ADM_REQUEST_FOR_SERVERS[config('app.url')];
        }

        return 0;
    }


    /**
     * Save account
     *
     * @param  SettingsRequest  $request
     * @param  Authenticatable|null  $user
     *
     * @return mixed
     * @throws Exception
     */
    public static function saveAccount(SettingsRequest $request, ?Authenticatable $user = null)
    {

        return (new AccessService())->setRequest($request)->run($user);
    }

    /**
     * Get platform
     *
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Get users
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_account')->withPivot(['is_account_admin', 'is_selected']);
    }

    /**
     * Get first selected user id
     */
    public function userId(){
        foreach ($this->belongsToMany(User::class, 'user_account')->get() as $user) {
            if (isset($user->pivot->is_selected) && $user->pivot->is_selected && $user->pivot->is_admin) {
                return $user->id ?? null;
            }
        }
        return $user->id ?? null;
    }

    /**
     * Calculate field platform_title
     * @return mixed
     */
    public function getPlatformTitleAttribute()
    {
        return $this->platform->title;
    }


    /**
     * Scope query to only include account of given platform id
     *
     * @param $query
     * @param  int  $platformId
     *
     * @return void
     */
    public function scopeOfPlatform($query, int $platformId)
    {
        $query->where('platform_id', $platformId);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'account_company')
            ->withPivot('company_id', 'account_id');
    }
}
