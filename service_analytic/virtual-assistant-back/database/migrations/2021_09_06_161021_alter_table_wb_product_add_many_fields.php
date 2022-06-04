<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductAddManyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            if (Schema::hasColumns('wb_products', ['user_id'])) {
                Schema::dropColumns('wb_products', ['user_id']);
            }
            if (Schema::hasColumns('wb_products', ['card_user_id'])) {
                Schema::dropColumns('wb_products', ['card_user_id']);
            }
            $table->unsignedBigInteger('user_id')->index()->nullable()->after('id')->comment('ID пользователя для связи с таблицами');
            $table->unsignedBigInteger('card_user_id')->index()->nullable()->after('imt_id')->comment('ID пользователя в системе WB');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            //
        });
    }
}
