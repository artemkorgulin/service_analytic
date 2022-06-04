<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbProductUser extends Model
{
    use HasFactory, SoftDeletes;

    const PRODUCT_RELATION_FIELD = 'imt_id';

    protected $table = 'wb_product_user';

    public $fillable = ['user_id', 'account_id', 'external_id', 'deleted_at'];

    public $timestamps = false;


    /**
     * Scope Wildberries product for current account
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query)
    {
        return UserService::getAccountId() ? $query->where('account_id', UserService::getAccountId()) : $query;
    }


    /**
     * Scope Wildberries product for current user
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        return UserService::getUserId() ? $query->where('user_id', UserService::getUserId()) : $query;
    }

    /**
     * Scope Wildberries product for current user and account
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUserAndAccount($query)
    {
        return UserService::getUserId() && UserService::getAccountId() ?
            $query->where([
                'user_id' => UserService::getUserId(),
                'account_id' => UserService::getAccountId(),
            ]) : $query;
    }

}
