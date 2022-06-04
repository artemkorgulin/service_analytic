<?php

use Illuminate\Database\Migrations\Migration;

class SetAccountIdInOzProductsTableFromVirtualAssistantDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userIds = DB::connection('va')->table('oz_products AS op')
            ->whereNull(['op.account_id'])
            ->whereNotNull('op.user_id')
            ->select([DB::raw('DISTINCT user_id AS id')])
            ->get()
            ->pluck('id')
            ->toArray();

        foreach ($userIds as $id) {
            $accountId = optional(
                DB::table('users AS u')
                    ->leftJoin('user_account AS ua', 'u.id', '=', 'ua.user_id')
                    ->leftJoin('accounts AS a', 'ua.account_id', '=', 'a.id')
                    ->where([['u.id', '=', $id], ['a.platform_id', '=', 1]])
                    ->whereNotNull('a.id')
                    ->first()
            )->id;

            if (!empty($accountId)) {
                DB::connection('va')->table('oz_products AS op')
                    ->where('op.user_id', '=', $id)
                    ->whereNull(['op.account_id'])
                    ->update(['account_id' => $accountId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
