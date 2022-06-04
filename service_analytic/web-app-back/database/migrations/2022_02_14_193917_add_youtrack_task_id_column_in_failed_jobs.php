<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYoutrackTaskIdColumnInFailedJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->string('youtrack_task_id')->nullable()->comment('Номер задачи в Youtrack');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Id первой ошибки');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropColumn(['youtrack_task_id', 'parent_id']);
        });
    }
}
