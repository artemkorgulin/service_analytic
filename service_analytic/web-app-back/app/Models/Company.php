<?php

namespace App\Models;

use App\Services\Billing\OrderService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected $fillable = ['name', 'inn', 'kpp', 'ogrn', 'address'];

    /**
     * Get accounts
     *
     * @return BelongsToMany
     */
    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')->withTimestamps()->wherePivot('deleted_at', null);
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->usersWithTrashed()
            ->wherePivot('deleted_at', null)
            ->wherePivot('is_active', 1);
    }

    /**
     * @return BelongsToMany
     */
    public function usersWithTrashed(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')
            ->withPivot('company_id', 'user_id', 'deleted_at', 'is_selected', 'is_active', 'selected_account_id', 'created_at');
    }

    /**
     * Заказы сформированные для компании.
     *
     * @return HasMany
     */
    public function directOrders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return BelongsToMany
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_company')
            ->withPivot('company_id', 'account_id');
    }

    /**
     * @return bool
     */
    public function getCorporateAccessAttribute(): bool
    {
        $orderService = new OrderService();

        return $orderService->hasService($this, 'corp');
    }

    /**
     * @return HasMany
     */
    public function userCompanies(): HasMany
    {
        return $this->hasMany(UserCompany::class);
    }

    /**
     * @return User|null
     */
    public function getOwner(): User|null
    {
        return $this->userCompanies()->whereHas('roles', function ($query) {
            $query->where('name', '=', 'company.owner');
        })->first()?->user;
    }
}
