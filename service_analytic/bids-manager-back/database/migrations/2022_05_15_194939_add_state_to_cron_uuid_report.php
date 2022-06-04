<?php

use App\Enums\OzonPerformance\Campaign\CampaignReportState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStateToCronUuidReport extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table
                ->enum('state', CampaignReportState::keys())
                ->default(CampaignReportState::NOT_STARTED()->getKey())
                ->after('processed')
                ->comment('Статус готовности отчёта');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
}
