<?php

use App\Models\CampaignStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampaignStatusesSortColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_statuses', function (Blueprint $table) {
            $table->integer('sort')->nullable();
        });

        CampaignStatus::query()->update(['sort' => DB::raw('id * 10')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_statuses', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
