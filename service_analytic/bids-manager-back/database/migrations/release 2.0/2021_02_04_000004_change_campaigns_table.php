<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->integer('ozon_id')->nullable()->after('id');
            $table->foreignId('payment_type_id')->nullable()->after('station_type'); // Справочник тип оплаты
            $table->foreignId('placement_id')->nullable()->after('payment_type_id'); // Справочник место размещения
            $table->foreignId('page_type_id')->nullable()->after('station_type');    // Справочник тип страницы

            $table->foreign('payment_type_id')->references('id')->on('campaign_payment_types')
                ->onUpdate('cascade');
            $table->foreign('placement_id')->references('id')->on('campaign_placements')
                ->onUpdate('cascade');
            $table->foreign('page_type_id')->references('id')->on('campaign_page_types')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('ozon_id');
            $table->dropColumn('payment_type_id');
            $table->dropColumn('placement_id');
            $table->dropColumn('page_type_id');

            $table->dropForeign('campaigns_payment_type_id_foreign_id');
            $table->dropForeign('campaigns_placement_id_foreign_id');
            $table->dropForeign('campaigns_page_type_id_foreign_id');
        });
    }
}
