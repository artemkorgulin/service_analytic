<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class UserCompany extends Model
{
    use HasFactory, SoftDeletes, HasPermissions, HasRoles;

    protected $table = 'user_companies';

    protected $guard_name = 'company';

    protected $fillable = [
        'company_id',
        'user_id',
        'deleted_at',
    ];

    /**
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return belongsTo
     */
    public function company(): belongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return Account|null
     */
    public function getSelectedAccount(): Account|null
    {
        return $this->company->accounts()->where('accounts.id', $this->selected_account_id)->first();
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeActive($query)
    {
        $query->where('is_active', true);
    }
}
