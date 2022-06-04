<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TariffPhoneModalShown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('tariff_phone_modal_shown')->default(false);
            $table->timestamp('tariff_phone_modal_shown_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tariff_phone_modal_shown');
            $table->dropColumn('tariff_phone_modal_shown_at');
        });
    }
}
