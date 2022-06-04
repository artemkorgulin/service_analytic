<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEscrowHashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escrow_hashes', function (Blueprint $table) {
            $table->bigInteger('nmid')->nullable()->index();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escrow_hashes', function (Blueprint $table) {
            $table->dropColumn('nmid');
        });
    }
}
