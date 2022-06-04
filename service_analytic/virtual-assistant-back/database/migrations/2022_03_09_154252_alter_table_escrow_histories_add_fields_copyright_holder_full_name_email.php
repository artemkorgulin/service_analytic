<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEscrowHistoriesAddFieldsCopyrightHolderFullNameEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escrow_histories', function (Blueprint $table) {
            $table->string('copyright_holder')->comment('Правообладатель');
            $table->string('full_name')->comment('ФИО');
            $table->string('email')->comment('E-mail');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escrow_histories', function (Blueprint $table) {
            $table->dropColumn('copyright_holder');
            $table->dropColumn('full_name');
            $table->dropColumn('email');
        });
    }
}
