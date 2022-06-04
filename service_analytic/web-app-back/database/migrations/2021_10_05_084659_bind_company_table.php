<?php

use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;

class BindCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('companies')->insertOrIgnore([
            'name' => 'ЕСО-МАРКЕТ ООО',
            'inn' => '2124041927',
            'kpp' => '212401001',
            'ogrn' => '1162130052920'
        ]);
        $company1 = DB::table('companies')->where('ogrn', '=', '1162130052920')->first();
        DB::table('companies')->insertOrIgnore([
            'name' => 'ООО ЭВЕРЕСТ',
            'inn' => '7719467365',
            'kpp' => '771901001',
            'ogrn' => '1177746260889'
        ]);
        $company2 = DB::table('companies')->where('ogrn', '=', '1177746260889')->first();
        $users = DB::table('users')->get()->all();
        $insertData = [];
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        foreach ($users as $user) {
            $insertData[] = [
                'user_id' => $user->id,
                'company_id' => $company1->id,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ];
            $insertData[] = [
                'user_id' => $user->id,
                'company_id' => $company2->id,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ];
        }
        DB::table('user_companies')->insertOrIgnore($insertData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = DB::table('users')->get()->all();
        $company1 = DB::table('companies')->where(['name' => 'ЕСО-МАРКЕТ ООО', 'inn' => '2124041927', 'kpp' => '212401001', 'ogrn' => '1162130052920'])->first();
        $company2 = DB::table('companies')->where(['name' => 'ООО ЭВЕРЕСТ', 'inn' => '7719467365', 'kpp' => '771901001', 'ogrn' => '1177746260889'])->first();
        foreach ($users as $user){
            DB::table('user_companies')->where(['user_id' => $user->id, 'company_id' => $company1->id])->delete();
            DB::table('user_companies')->where(['user_id' => $user->id, 'company_id' => $company2->id])->delete();
        }
        DB::table('companies')->where(['name' => 'ЕСО-МАРКЕТ ООО', 'inn' => '2124041927', 'kpp' => '212401001', 'ogrn' => '1162130052920'])->delete();
        DB::table('companies')->where(['name' => 'ООО ЭВЕРЕСТ', 'inn' => '7719467365', 'kpp' => '771901001', 'ogrn' => '1177746260889'])->delete();
    }
}
