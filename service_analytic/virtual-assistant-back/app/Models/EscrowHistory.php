<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EscrowHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'product_id',
        'copyright_holder',
        'full_name',
        'email',
        'nmid'
    ];

    /**
     * Check user limit of escrows by current account
     *
     * @return int
     */
    public static function countForCurrentAccount(): int
    {
        return self::where('account_id', UserService::getAccountId())->count();
    }

    /**
     * Check user limit of escrows by all accounts
     *
     * @return int
     */
    public static function countForAllUserAccounts(): int
    {
        $count = 0;
        $accounts = UserService::getAccounts();
        if (!empty($accounts)) {
            foreach ($accounts as $account) {
                $accountIds[] = $account['id'];
            }
            if (!empty($accountIds)) {
                $count = self::whereIn('account_id', $accountIds)->count();
            }
        }
        return $count;
    }
}
