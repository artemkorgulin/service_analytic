<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCronUuidReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_uuid_report', function(Blueprint $table) {
            $table->boolean('processed')->default(false)->comment('Обработано?');
        });

        DB::table('cron_uuid_report')->update(['processed' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cron_uuid_report', function(Blueprint $table) {
            $table->dropColumn('processed');
        });
    }
}
