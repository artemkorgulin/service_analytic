<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveUserAccountForDeletedAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('user_account')
            ->join('accounts', 'user_account.account_id', '=', 'accounts.id')
            ->whereNotNull('accounts.deleted_at')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //what is done is done
        //there is no way to rollback deleted data
    }
}
