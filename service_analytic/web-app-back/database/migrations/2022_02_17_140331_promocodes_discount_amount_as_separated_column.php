<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PromocodesDiscountAmountAsSeparatedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->integer('discount')->default(0)->comment('% скидка по промокоду');
            $table->dropColumn('type_c');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->integer('type_c')
                  ->comment('Количественный параметр (% скидки, количество дней бонуса,'.
                            ' количество добавляемых SKU, количество единиц для пополнения баланса');
            $table->dropColumn('discount');
        });
    }
}
