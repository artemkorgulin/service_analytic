<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCronUuidReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::table('cron_uuid_phrase', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign('cron_uuid_report_account_id_foreign');
            $table->dropColumn('account_id');
        });
        Schema::table('cron_uuid_phrase', function (Blueprint $table) {
            $table->dropForeign('cron_uuid_phrase_account_id_foreign');
            $table->dropColumn('account_id');
        });
    }
}
