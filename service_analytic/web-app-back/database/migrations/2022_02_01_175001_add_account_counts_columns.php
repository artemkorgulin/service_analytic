<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAccountCountsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['old_ads_account_id']);
            $table->integer('max_count_request_per_day')->after('params')->default(0)
                ->comment('Максимальное число запросов к API в день');
            $table->integer('current_count_request')->after('params')->default(0)
                ->comment('Число запросов к API за текущий день');
            $table->date('last_request_day')->nullable()->after('params')->comment('Дата последнего запроса к маркетплэйсу');
        });

        DB::table('accounts')
            ->where('platform_id', '=', 2)
            ->update(['max_count_request_per_day' => Account::COUNT_ADM_REQUEST_FOR_SERVERS[config('app.url')]]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('old_ads_account_id')->nullable();
            $table->dropColumn(['max_count_request_per_day', 'current_count_request', 'last_request_day']);
        });
    }
}
