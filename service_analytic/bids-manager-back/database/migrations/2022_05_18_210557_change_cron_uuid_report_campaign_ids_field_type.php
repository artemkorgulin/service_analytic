<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ChangeCronUuidReportCampaignIdsFieldType extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->renameColumn('campaigns_ids', 'str_campaign_ids');
            $table->json('campaign_ids')
                ->default(new Expression('(json_array())'))
                ->comment('Массив id рекламных кампаний в отчёте');
        });

        DB::table('cron_uuid_report')->update(['campaign_ids' => DB::raw('cast(concat("[", str_campaign_ids, "]") as json)')]);

        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->dropColumn('str_campaign_ids');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->string('campaigns_ids')->nullable();
        });

        DB::table('cron_uuid_report')->orderBy('id')->chunk(300, function (Collection $rows) {
            $rowsToUpdate = [];
            foreach ($rows as $row) {
                $row->campaigns_ids = substr($row->campaign_ids, 1, -1);
                $rowsToUpdate[]     = (array) $row;
            }
            DB::table('cron_uuid_report')->upsert($rowsToUpdate, 'id', ['campaigns_ids']);
        });

        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->dropColumn('campaign_ids');
        });
    }
}
