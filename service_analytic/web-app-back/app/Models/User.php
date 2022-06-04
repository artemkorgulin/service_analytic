<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

use App\Helpers\PhoneHelper;


/**
 * Пользователь
 * @property int id
 * @property string name
 * @property string email
 * @property DateTime email_verified_at
 * @property string password
 * @property string api_token
 * @property string remember_token
 * @property string verification_token
 * @property bool enable_email_verifications
 * @property bool first_login
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property mixed accounts
 */
class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, SoftDeletes, HasPermissions, HasRoles, Actionable;

    protected $guard_name = 'admin';

    protected $dates = ['deleted_at'];

    protected $appends = [
        'parent'
    ];


    public function getParentAttribute()
    {
        return $this->accounts()->wherePivot('is_selected', '=', 1)
            ->wherePivot('is_account_admin', '=', 1)
            ->first();
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'login',
        'email',
        'unverified_phone',
        'password',
        'enable_email_notifications',
        'ads_account_id',
        'first_login'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_verification_token_created_at' => 'datetime',
        'tariff_phone_modal_shown' => 'boolean',
        'tariff_phone_modal_shown_at' => 'datetime',
    ];


    /**
     * Get orders
     *
     * @return BelongsToMany
     */
    public function order(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'tariff_activity');
    }

    /**
     * @return hasMany
     */
    public function orders(): hasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get orders paid && activate
     * @return BelongsToMany
     */
    public function paidOrders(): BelongsToMany
    {
        return $this->BelongsToMany(Order::class, 'tariff_activity')
            ->withPivot('order_id', 'status', 'id')
            ->wherePivot('status', TariffActivity::ACTVE);
    }


    /**
     * @return HasMany
     */
    public function tariffActivities(): HasMany
    {
        return $this->hasMany(TariffActivity::class);
    }


    /**
     * User tariffs
     * @return BelongsToMany
     */
    public function tariffs(): BelongsToMany
    {
        return $this->belongsToMany(OldTariff::class, 'tariff_activity')
            ->as('activity')
            ->withPivot('status', 'start_at', 'end_at');
    }


    /**
     * Active user tariffs
     * @return BelongsToMany
     */
    public function activeTariffs(): BelongsToMany
    {
        return $this->tariffs()->wherePivot('status', true);
    }


    /**
     * @return HasOne
     */
    public function userInfo(): HasOne
    {
        return $this->hasOne(UserInfo::class);
    }


    /**
     * Get accounts
     *
     * @return BelongsToMany
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'user_account')->withPivot('is_account_admin', 'is_selected');
    }


    /**
     * Get current user model
     *
     * @return User|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getCurrentUser()
    {
        $adminUser = \Auth::guard('admin')->user();

        if ($adminUser) {
            return $adminUser;
        }

        $apiUser = \Auth::guard('api')->user();

        if ($apiUser) {
            return $apiUser;
        }

        return \Auth::user();
    }


    /**
     * Get filter user attributes
     *
     * @param array $exceptedAttributes
     *
     * @return array
     */
    public function getFilteredProxyAttributes(array $exceptedAttributes = []): array
    {
        $filterAttributes = array_merge([
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
            'verification_token',
            'ads_account_id',
            'bids_manager_token',
            'virtual_assistant_token',
            'ozon_supplier_api_key',
            'ozon_supplier_id',
            'ozon_client_api_key',
            'ozon_client_id',
            'enable_email_notifications',
            'is_active',
            'balance',
            'type',
            'first_login'
        ], $exceptedAttributes);

        $attributes = new Collection($this->getAttributes());

        return $attributes->except($filterAttributes)->all();
    }

    /**
     * Get accounts
     *
     * @return BelongsToMany
     */
    public function company(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies')
            ->withPivot('user_id', 'company_id', 'is_selected', 'is_active', 'selected_account_id')
            ->wherePivot('deleted_at', null)
            ->wherePivot('is_active', 1);
    }

    /**
     * @return HasMany
     */
    public function userCompanies(): HasMany
    {
        return $this->hasMany(UserCompany::class);
    }

    /**
     * @return Company|null
     */
    public function getSelectedCompany(): Company|null
    {
        return $this->company()->where('is_selected', 1)->first();
    }

    /**
     * @return Account|null
     */
    public function getSelectedUserAccount(): Account|null
    {
        return $this->accounts()->wherePivot('is_selected', 1)->first();
    }

    /**
     * @return Account|null
     */
    public function getSelectedUserAccountDependingContext(): Account|null
    {
        if ($selectedCompany = $this->getSelectedCompany()) {
            return $this->userCompanies()->where('company_id', $selectedCompany->id)->first()?->getSelectedAccount();
        }

        return $this->getSelectedUserAccount();
    }

    /**
     * @param int $companyId
     * @return HasMany
     */
    public function userCompany(int $companyId): HasMany
    {
        return $this->userCompanies()->where('company_id', $companyId);
    }

    /**
     * Получить промокоды в базе данных, что ассоциированы с этим человеком
     *
     * @return BelongsToMany
     */
    public function promocodes(): BelongsToMany
    {
        return $this->belongsToMany(Promocode::class, 'promocode_users')
            ->withPivot('order_id', 'created_at');
    }


    /**
     * Получить историю применения промокодов
     */
    public function promocodeUsers(): HasMany
    {
        return $this->hasMany(PromocodeUser::class);
    }


    /**
     * Есть ли неиспользованные промокоды у пользователя?
     *
     * @return boolean
     */
    public function haveUnusedDiscountPromocode()
    {
        return $this->promocodeUsers()
            ->whereNull('order_id')
            ->exists();
    }


    /**
     * Получить следующий свободный промокод у пользователя
     */
    public function nextDiscountPromocode()
    {
        return $this->promocodeUsers()
            ->whereNull('order_id')
            ->first();
    }


    /**
     * Scope query to only get users
     * who have paid tariff
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeWithPaidTariff(Builder $query): void
    {
        $query->whereHas('tariffActivities', function (Builder $query) {
            $query->where('status', '=', TariffActivity::ACTVE);
        });
    }

    /**
     * Заказы сформированные непосредственно этим человеком
     */
    public function directOrders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Выпустить новый токен для подтверждения телефона
     */
    public function issueNewPhoneVerificationToken()
    {
        $this->phone_verified_at = null;
        $this->phone_verification_token = PhoneHelper::generateVerificationToken();
        $this->phone_verification_token_created_at = Carbon::now();
    }
}
