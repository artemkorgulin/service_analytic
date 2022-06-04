<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeRequiredFieldsInCampaignKeywordsAndCampaignStopWordsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('keyword_id')->nullable(false)->after('group_id')
                ->comment('Ссылка на таблицу keywords')->change();
            $table->unsignedBigInteger('status_id')->nullable(false)->after('group_id')->default(1)
                ->comment('Ссылка на таблицу statuses')->change();
            $table->dropColumn(['created_at', 'updated_at']);
        });

        DB::table('campaign_stop_words')->where('status_id', '<>', 1)->delete();
        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_good_id')->nullable()->after('updated_at')
                ->comment('Ссылка на таблицу campaign_goods')->change();
            $table->unsignedBigInteger('stop_word_id')->nullable(false)->after('stop_word_id')
                ->comment('Ссылка на таблицу stop_words')->change();
            $table->dropForeign('campaign_stop_words_status_id_foreign');
            $table->dropColumn(['created_at', 'updated_at', 'status_id']);
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('keyword_id')->nullable()->after('group_id')
                ->comment('')->change();
            $table->unsignedBigInteger('status_id')->nullable()->after('keyword_id')
                ->comment('')->change();
            $table->timestamps();
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_good_id')->nullable(false)->after('updated_at')
                ->comment('')->change();
            $table->unsignedBigInteger('stop_word_id')->nullable()->after('group_id')
                ->comment('')->change();
            $table->unsignedBigInteger('status_id')->nullable(false)->after('group_id')->default(1);
            $table->timestamps();
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('statuses')->onUpdate('cascade')->onDelete('restrict');
        });
        Schema::enableForeignKeyConstraints();
    }
}
