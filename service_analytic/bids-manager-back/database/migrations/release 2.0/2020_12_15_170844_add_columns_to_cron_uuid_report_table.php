<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCronUuidReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->string('date_from')->nullable();
            $table->string('date_to')->nullable();
            $table->string('campaigns_ids')->nullable();
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
            $table->dropColumn('date_from');
            $table->dropColumn('date_to');
            $table->dropColumn('campaigns_ids');
        });
    }
}
