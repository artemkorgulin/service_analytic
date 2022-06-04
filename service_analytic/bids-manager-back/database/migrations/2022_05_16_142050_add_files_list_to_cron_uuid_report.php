<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilesListToCronUuidReport extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_uuid_report', function (Blueprint $table) {
            $table->json('files')->nullable()->comment('Пути до скачанных файлов отчёта');
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
            $table->dropColumn('files');
        });
    }
}
