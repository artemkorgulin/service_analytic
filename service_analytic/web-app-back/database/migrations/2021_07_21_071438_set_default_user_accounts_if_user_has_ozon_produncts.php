<?php

use Illuminate\Database\Migrations\Migration;

class SetDefaultUserAccountsIfUserHasOzonProduncts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $usersWithProducts = DB::connection('va')->table('oz_products')
            ->groupBy(['user_id'])
            ->pluck('user_id')
            ->toArray();
        $defaultVAAccount = DB::connection('mysql')->table('accounts')
            ->where('title', '=', 'AnalyticPlatform Ozon')
            ->first()->id;

        if (!empty($usersWithProducts) && !empty($defaultVAAccount)) {
            $usersWithoutAccounts = DB::connection('mysql')->table('users')
                ->leftJoin('user_account AS ua', 'users.id', '=', 'ua.user_id')
                ->whereIn('users.id', $usersWithProducts)
                ->whereNull(['ua.account_id'])->orderBy('users.id')->pluck('users.id')->toArray();
            $insertData = [];

            foreach ($usersWithoutAccounts as $userId) {
                $insertData[] = ['user_id' => $userId, 'account_id' => $defaultVAAccount];
            }

            DB::table('user_account')->insert($insertData);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
