<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbNomenclatureAddFieldUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_nomenclatures', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index()->nullable()->after('id')
                ->comment('user id ссылка - возможно потом не понадобится');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wb_nomenclatures', function (Blueprint $table) {
            //
            if (Schema::hasColumn('wb_nomenclatures', 'user_id'))
                Schema::dropColumns('wb_nomenclatures', ['user_id']);
        });
    }
}
