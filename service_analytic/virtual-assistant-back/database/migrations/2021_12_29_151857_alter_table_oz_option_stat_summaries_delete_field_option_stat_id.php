<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzOptionStatSummariesDeleteFieldOptionStatId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_option_stat_summaries', function (Blueprint $table) {
            if (MigrationHelper::hasForeign('oz_option_stat_summaries',
                'oz_option_stat_summaries_option_stat_id_foreign')) {
                $table->dropConstrainedForeignId('option_stat_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_option_stat_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_option_stat_summaries', 'option_stat_id')) {
                $table->unsignedBigInteger('option_stat_id')->index()->nullable()
                    ->comment('Поле для foreign key');
            }
            if (!MigrationHelper::hasForeign('oz_option_stat_summaries',
                'oz_option_stat_summaries_option_stat_id_foreign')) {
                $table->foreign('option_stat_id', 'oz_option_stat_summaries_option_stat_id_foreign')
                    ->on('oz_option_stats')->references('id');
            }
        });
    }
}
