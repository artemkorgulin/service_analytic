<?php

use App\Enums\OzonPerformance\Campaign\CampaignState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStateFieldToCampaigns extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('state', CampaignState::keys())->after('name')->nullable();
        });

        DB::update(<<<sql
update campaigns c
inner join campaign_statuses cs on c.campaign_status_id = cs.id
set c.state = cs.ozon_code;
sql
        );
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
}
